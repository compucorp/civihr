define([
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'common/services/api/option-group',
  'common/services/hr-settings',
  'common/models/contact',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/public-holiday-model',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/models/instances/sickness-leave-request-instance',
], function (components, _, moment) {
  'use strict';

  components.controller('LeaveRequestPopupCtrl', [
    '$log', '$q', '$rootScope', '$uibModalInstance', 'Contact', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'directiveOptions', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'LeaveRequestInstance', 'PublicHoliday', 'SicknessRequestInstance', 'shared-settings',
    function ($log, $q, $rootScope, $modalInstance, Contact, AbsencePeriod, AbsenceType,
      OptionGroup, directiveOptions, Calendar, Entitlement, HR_settings,
      LeaveRequest, LeaveRequestInstance, PublicHoliday, SicknessRequestInstance, sharedSettings
    ) {
      $log.debug('LeaveRequestPopupCtrl');

      var absenceTypesAndIds,
        initialLeaveRequestAttributes = {}, //used to compare the change in leaverequest in edit mode
        mode = '', //can be edit, create, view
        role = '', //could be manager, owner or admin
        selectedAbsenceType = {},
        leaveType = 'leave', //other values could be sick or toil
        vm = {};

      vm.absencePeriods = [];
      vm.absenceTypes = [];
      vm.calendar = {};
      vm.contact = {};
      vm.error = null;
      vm.leaveRequestDayTypes = [];
      vm.period = {};
      vm.statusLabel = '';
      vm.balance = {
        closing: 0,
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        }
      };
      vm.loading = {
        absenceTypes: true,
        calculateBalanceChange: false,
        fromDayTypes: false,
        toDayTypes: false
      };
      vm.pagination = {
        currentPage: 1,
        filteredbreakdown: vm.balance.change.breakdown,
        numPerPage: 5,
        totalItems: vm.balance.change.breakdown.length,
        /**
         * Called when user changes the page under selection. It filters the
         * breakdown to obtain the ones for currently selected page.
         */
        pageChanged: function () {
          //filter breakdowns
          var begin = (this.currentPage - 1) * this.numPerPage,
            end = begin + this.numPerPage;

          this.filteredbreakdown = vm.balance.change.breakdown.slice(begin, end);
        }
      };
      vm.uiOptions = {
        isChangeExpanded: false,
        multipleDays: true,
        workedDate: null,
        userDateFormat: HR_settings.DATE_FORMAT,
        showBalance: false,
        date: {
          from: {
            show: false,
            options: {
              startingDay: 1,
              showWeeks: false
            }
          },
          to: {
            show: false,
            options: {
              minDate: null,
              maxDate: null,
              startingDay: 1,
              showWeeks: false
            }
          },
          // temporary, for PCHR-1384
          dateWorked: {
            show: false,
            options: {
              minDate: null,
              maxDate: null,
              startingDay: 1,
              showWeeks: false
            }
          },
        }
      };
      // temporary, for PCHR-1384
      vm.currentDate = '09/02/2017';

      /**
       * Change handler for change request type like multiple or single. It will
       * reset dates, day types, change balance.
       */
      vm.changeInNoOfDays = function () {
        reset();
        //reinitialize opening balance
        initAbsenceType();
      };

      /**
       * When user cancels the model dialog
       */
      vm.cancel = function () {
        $modalInstance.dismiss({
          $value: 'cancel'
        });
      };

      /**
       * Closes the error alerts if any
       */
      vm.closeAlert = function () {
        vm.error = null;
      };

      /**
       * Calculate change in balance, it updates local balance variables.
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      vm.calculateBalanceChange = function () {
        setDateAndTypes();

        if (!canCalculateChange()) {
          return $q.resolve();
        }

        vm.error = null;
        vm.loading.calculateBalanceChange = true;
        return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange())
          .then(function (balanceChange) {
            if (balanceChange) {
              vm.balance.change = balanceChange;
              //the change is negative so adding it will actually subtract it
              vm.balance.closing = vm.balance.opening + vm.balance.change.amount;
              rePaginate();
            }
            vm.loading.calculateBalanceChange = false;
          })
          .catch(handleError);
      };

      /**
       * Checks if submit button can be enabled for user and returns true if succeeds
       *
       * @return {Boolean}
       */
      vm.canSubmit = function () {
        var canSubmit = canCalculateChange();

        //check if user has changed any attribute
        if (vm.isMode('edit')) {
          canSubmit = canSubmit && !_.isEqual(initialLeaveRequestAttributes, vm.leaveRequest.attributes());
        }

        //check if manager has changed status
        if (vm.isRole('manager') && vm.leaveRequestStatuses) {
          //waiting_approval will not be available in vm.leaveRequestStatuses if manager has changed selection
          canSubmit = canSubmit && !!getStatusFromValue(vm.leaveRequest.status_id);
        }

        if (vm.isLeaveType('sick')) {
          canSubmit = canSubmit && !!vm.leaveRequest.reason;
        }

        return canSubmit && !vm.isMode('view');
      };

      /**
       * Checks if given value is set for leave request list of document value ie., field required_documents
       *
       * @param {String} value
       * @return {Boolean}
       */
      vm.isDocumentInRequest = function (value) {
        return !!_.find(vm.sicknessDocumentTypes, function (document) {
          return document.value == value;
        });
      };

      /**
       * Checks if popup is opened in given leave type like `leave` or `sick`
       *
       * @param {String} leaveTypeParam to check the leave type of current request
       * @return {Boolean}
       */
      vm.isLeaveType = function (leaveTypeParam) {
        return leaveType === leaveTypeParam;
      };

      /**
       * Checks if popup is opened in given mode
       *
       * @param {String} modeParam to open leave request like edit or view or create
       * @return {Boolean}
       */
      vm.isMode = function (modeParam) {
        return mode === modeParam;
      };

      /**
       * Checks if popup is opened in given role
       *
       * @param {String} roleParam like manager, owner
       * @return {Boolean}
       */
      vm.isRole = function (roleParam) {
        return role === roleParam;
      };

      /**
       * Dismiss modal on successful creation on submit
       */
      vm.ok = function () {
        //todo handle closure to pass data back to callee
        $modalInstance.close({
          $value: vm.leaveRequest
        });
      };

      /**
       * Submits the form, only if the leave request is valid, also emits event
       * to listeners that leaverequest is created.
       * Also, checks if its an update request from manager and accordingly updates leave request
       */
      vm.submit = function () {
        if (vm.isMode('view')) {
          return;
        }

        // current absence type (vm.leaveRequest.type_id) doesn't allow that
        if (vm.balance.closing < 0 && selectedAbsenceType.allow_overuse == '0') {
          // show an error
          vm.error = 'You are not allowed to apply leave in negative';
          return;
        }

        vm.error = null;
        //update leaverequest

        if (canViewOrEdit()) {
          updateRequest();
        } else {
          createRequest();
        }
      };

      /**
       * This should be called whenever a date has been changed
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @param {String} dayType - set to from if from date is selected else to
       * @return {Promise}
       */
      vm.updateAbsencePeriodDatesTypes = function (date, dayType) {
        var oldPeriodId = vm.period.id;
        dayType = dayType || 'from';
        vm.loading[dayType + 'DayTypes'] = true;

        return checkAndSetAbsencePeriod(date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId == vm.period.id;

            if (!isInCurrentPeriod) {
              //partial reset is required when user has selected a to date and
              //then changes absence period from from date
              //no reset required for single days and to date changes
              if (vm.uiOptions.multipleDays && dayType === 'from') {
                vm.uiOptions.showBalance = false;
                vm.uiOptions.toDate = null;
                vm.leaveRequest.to_date = null;
                vm.leaveRequest.to_date_type = null;
              }

              return $q.all([
                setAbsenceTypesFromEntitlements(),
                loadCalendar()
              ]);
            }
          })
          .then(function () {
            setMinMax();

            return filterLeaveRequestDayTypes(date, dayType);
          })
          .then(function () {
            vm.loading[dayType + 'DayTypes'] = false;

            return vm.updateBalance();
          })
          .catch(function (error) {
            vm.error = error;
          });
      };

      /**
       * Whenever the absence type changes, update the balance opening.
       * Also the balance change needs to be recalculated, if the `from` and `to`
       * dates have been already selected
       */
      vm.updateBalance = function () {
        selectedAbsenceType = getSelectedAbsenceType();
        // get the `balance` of the newly selected absence type
        vm.balance.opening = selectedAbsenceType.remainder;

        vm.calculateBalanceChange();
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;
        initLeaveType();
        initLeaveRequest();

        loadStatuses()
          .then(function () {
            initUserRole();
            initOpenMode();
            return loadAbsencePeriods();
          })
          .then(function () {
            initAbsencePeriod();
            setMinMax();

            return $q.all([
              loadAbsenceTypes(),
              loadCalendar()
            ]);
          })
          .then(function () {
            return loadDayTypes();
          })
          .then(function () {
            return $q.all([
              initDates(),
              loadDocuments(),
              loadReasons()
            ]);
          })
          .then(function () {
            initAbsenceType();
            initStatus();
            initContact();

            if (vm.isMode('edit')) {
              initialLeaveRequestAttributes = vm.leaveRequest.attributes();
            }
          })
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      /**
       * Checks if all params are set to calculate balance
       *
       * @param {Boolean} true if all present else false
       */
      function canCalculateChange() {
        return !!vm.leaveRequest.from_date && !!vm.leaveRequest.to_date &&
          !!vm.leaveRequest.from_date_type && !!vm.leaveRequest.to_date_type;
      }

      /**
       * Creates leaverequest
       */
      function createRequest() {
        vm.leaveRequest.isValid()
          .then(function () {
            vm.leaveRequest.create()
              .then(function () {
                // refresh the list
                postSubmit('LeaveRequest::new');
              })
              .catch(handleError);
          })
          .catch(handleError);
      }

      /**
       * Checks if user can view or edit leaverequest
       *
       * @return {Boolean}
       */
      function canViewOrEdit() {
        return vm.isMode('edit') || vm.isMode('view');
      }

      /**
       * Finds if date is in any absence period and sets absence period for the given date
       *
       * @param {Date/String} date
       * @return {Promise} with true value if period found else rejected false
       */
      function checkAndSetAbsencePeriod(date) {
        var formattedDate = moment(date).format(vm.uiOptions.userDateFormat.toUpperCase());

        vm.period = _.find(vm.absencePeriods, function (period) {
          return period.isInPeriod(formattedDate);
        });

        if (!vm.period) {
          //inform user if absence period is not found
          return $q.reject('Please change date as it is not in any absence period');
        }

        return $q.resolve(true);
      }

      /**
       * Converts given date to server format
       *
       * @param {Date} date
       * @return {Date} converted to server format
       */
      function convertDateFormatToServer(date) {
        return moment(date).format(sharedSettings.serverDateFormat);
      }

      /**
       * Converts given date to javascript date as expected by uib-datepicker
       *
       * @param {Date/String} date from server
       * @return {Date} Javascript date
       */
      function convertDateFormatFromServer(date) {
        return moment(date, sharedSettings.serverDateFormat).clone().toDate();
      }

      /**
       * Filters absence type and formats data to be compatible with angular select directives
       *
       * @param {Array} absenceTypes
       * @param {Object} entitlements
       * @return {Array} of filtered absence types for given entitlements
       */
      function filterAbsenceTypes(absenceTypes, entitlements) {
        return entitlements.map(function (entitlementItem) {
          var absenceType = _.find(absenceTypes, function (absenceTypeItem) {
            return absenceTypeItem.id === entitlementItem.type_id;
          });

          return {
            id: entitlementItem.type_id,
            title: absenceType.title + ' ( ' + entitlementItem.remainder.current + ' ) ',
            remainder: entitlementItem.remainder.current,
            allow_overuse: absenceType.allow_overuse
          };
        });
      }

      /**
       * This method will be used on the view to return a list of available
       * leave request day types (All day, 1/2 AM, 1/2 PM, Non working day,
       * Weekend, Public holiday) for the given date (which is the date
       * selected by the user via datepicker)
       *
       * If no date is passed, then no list is returned
       *
       * @param  {Date} date
       * @param  {String} dayType - set to from if from date is selected else to
       * @return {Promise} of array with day types
       */
      function filterLeaveRequestDayTypes(date, dayType) {
        var deferred = $q.defer(),
          inCalendarList, listToReturn;

        if (!date) {
          deferred.reject([]);
        }

        // Make a copy of the list
        listToReturn = vm.leaveRequestDayTypes.slice(0);

        date = convertDateFormatToServer(date);
        PublicHoliday.isPublicHoliday(date)
          .then(function (result) {
            if (result) {
              listToReturn = listToReturn.filter(function (publicHoliday) {
                return publicHoliday.name === 'public_holiday';
              });
            } else {
              inCalendarList = getDayTypesFromDate(date, listToReturn);

              if (!inCalendarList.length) {
                // 'All day', '1/2 AM', and '1/2 PM' options
                listToReturn = listToReturn.filter(function (dayType) {
                  return dayType.name === 'all_day' || dayType.name === 'half_day_am' || dayType.name === 'half_day_pm';
                });
              } else {
                listToReturn = inCalendarList;
              }
            }

            setDayType(dayType, listToReturn);
            deferred.resolve(listToReturn);
          });

        return deferred.promise;
      }

      /**
       * Helper function to obtain params for leave request calculateBalanceChange api call
       *
       * @return {Object} containing required keys for leave request
       */
      function getParamsForBalanceChange() {
        return _.pick(vm.leaveRequest, ['contact_id', 'from_date',
          'from_date_type', 'to_date', 'to_date_type'
        ]);
      }

      /**
       * Gets list of day types if its found to be weekend or non working in calendar
       *
       * @param {Date} date to Checks
       * @param {Array} listOfDayTypes array of day types
       * @return {Array} non-empty if found else empty array
       */
      function getDayTypesFromDate(date, listOfDayTypes) {
        var listToReturn = [];

        try {
          if (vm.calendar.isNonWorkingDay(moment(date))) {
            listToReturn = listOfDayTypes.filter(function (day) {
              return day.name === 'non_working_day';
            });
          } else if (vm.calendar.isWeekend(moment(date))) {
            listToReturn = listOfDayTypes.filter(function (day) {
              return day.name === 'weekend';
            });
          }
        } catch (e) {
          listToReturn = [];
        }

        return listToReturn;
      }

      /**
       * Gets currently selected absence type from leave request type_id
       *
       * @return {Object} absence type object
       */
      function getSelectedAbsenceType() {
        return _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id == vm.leaveRequest.type_id;
        });
      }

      /**
       * Gets status object for given status value
       *
       * @param value of the status
       * @return {Object} option group of type status
       */
      function getStatusFromValue(value) {
        var key, foundStatus, keys = Object.keys(vm.leaveRequestStatuses);

        for (key in keys) {
          foundStatus = vm.leaveRequestStatuses[keys[key]];
          if (foundStatus.value == value) {
            return foundStatus;
          }
        }

        return foundStatus;
      }

      /**
       * Error handler, generally used in catch calls
       */
      function handleError(errors) {
        // show errors
        if (errors.error_message)
          vm.error = errors.error_message;
        else {
          vm.error = errors;
        }

        //reset loading Checks
        vm.loading.calculateBalanceChange = false;
        vm.loading.absenceTypes = false;
        vm.loading.fromDayTypes = false;
        vm.loading.toDayTypes = false;
      }

      /**
       * Initialize open mode of the dialog
       */
      function initOpenMode() {
        if (vm.leaveRequest.id) {
          mode = 'edit';

          //approved, admin_approved, rejected, cancelled
          var viewModes = [vm.leaveRequestStatuses['approved'].value, vm.leaveRequestStatuses['admin_approved'].value,
            vm.leaveRequestStatuses['rejected'].value, vm.leaveRequestStatuses['cancelled'].value
          ];

          if (vm.isRole('owner') && viewModes.indexOf(vm.leaveRequest.status_id) > -1) {
            mode = 'view';
          }

        } else {
          mode = 'create';
        }
      }

      /**
       * Initialize user's role
       */
      function initUserRole() {
        if (directiveOptions.leaveRequest &&
          directiveOptions.leaveRequest.contact_id != directiveOptions.contactId) {
          //check if manager is responding to leave request
          return setManagerRole(directiveOptions.contactId);
        }
        //owner is editing or viewing popup, no api call - direct set
        role = 'owner';
      }

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      function initLeaveRequest() {
        var attributes;

        //if set indicates that leaverequest is either being managed or edited
        if (directiveOptions.leaveRequest) {
          //get a clone so that it is not the same reference as passed from callee
          attributes = _.cloneDeep(directiveOptions.leaveRequest.attributes());
        } else {
          attributes = {
            contact_id: directiveOptions.contactId
          };
        }

        //init to get methods like roleOf again on leaverequest instance as cloning removes them
        if (vm.isLeaveType('sick')) {
          vm.leaveRequest = SicknessRequestInstance.init(attributes);
        } else {
          vm.leaveRequest = LeaveRequestInstance.init(attributes);
        }
      }

      /**
       * Inits leave type
       */
      function initLeaveType() {
        if (directiveOptions.leaveType && directiveOptions.leaveType !== 'holiday / vacation') {
          leaveType = directiveOptions.leaveType;
        } else {
          leaveType = 'leave';
        }
      }

      /**
       * Inits absence period for the current date
       */
      function initAbsencePeriod() {
        vm.period = _.find(vm.absencePeriods, function (period) {
          return period.current;
        });
      }

      /**
       * Initialize absence types
       */
      function initAbsenceType() {
        if (canViewOrEdit()) {
          selectedAbsenceType = getSelectedAbsenceType();
        } else {
          // Assign the first absence type to the leave request
          selectedAbsenceType = vm.absenceTypes[0];
          vm.leaveRequest.type_id = selectedAbsenceType.id;
        }

        // Init the `balance` object based on the first absence type
        vm.balance.opening = selectedAbsenceType.remainder;
      }

      /**
       * Initialize from and to dates and day types.
       * It will also set the day types.
       *
       * @return {Promise}
       */
      function initDates() {
        var deferred = $q.defer();

        if (canViewOrEdit()) {
          var attributes = vm.leaveRequest.attributes();

          vm.uiOptions.fromDate = convertDateFormatFromServer(vm.leaveRequest.from_date);

          vm.updateAbsencePeriodDatesTypes(vm.uiOptions.fromDate, 'from')
            .then(function () {
              //to_date and type has been reset in above call so reinitialize from clone
              vm.leaveRequest.to_date = attributes.to_date;
              vm.leaveRequest.to_date_type = attributes.to_date_type;
              vm.uiOptions.toDate = convertDateFormatFromServer(vm.leaveRequest.to_date);
              vm.updateAbsencePeriodDatesTypes(vm.uiOptions.toDate, 'to')
                .then(function () {
                  //resolve only after both from and to day types are also set
                  deferred.resolve();
                });
            });
        } else {
          deferred.resolve();
        }

        return deferred.promise;
      }

      /**
       * Initialize status
       */
      function initStatus() {
        if (canViewOrEdit()) {
          //set it before vm.leaveRequestStatuses gets filtered
          vm.statusLabel = getStatusFromValue(vm.leaveRequest.status_id).label;
          if (vm.isRole('manager')) {
            setStatuses();
          }
        } else if (vm.isMode('create')) {
          vm.leaveRequest.status_id = vm.leaveRequestStatuses['waiting_approval'].value;
        }
      }

      /**
       * Initialize contact
       *
       * {Promise}
       */
      function initContact() {
        if (vm.isRole('manager')) {
          return Contact.find(vm.leaveRequest.contact_id)
            .then(function (contact) {
              vm.contact = contact;
            });
        }

        return $q.resolve();
      }

      /**
       * Initializes user's calendar (work patterns)
       *
       * @return {Promise}
       */
      function loadCalendar() {
        return Calendar.get(vm.leaveRequest.contact_id, vm.period.id)
          .then(function (usersCalendar) {
            vm.calendar = usersCalendar;
          });
      }

      /**
       * Loads all absence periods
       */
      function loadAbsencePeriods() {
        return AbsencePeriod.all()
          .then(function (periods) {
            vm.absencePeriods = periods;
          });
      }

      /**
       * Initializes values for absence types and entitlements when the
       * leave request popup model is displayed
       *
       * @return {Promise}
       */
      function loadAbsenceTypes() {
        return AbsenceType.all({
            is_sick: vm.isLeaveType('sick')
          })
          .then(function (absenceTypes) {
            var absenceTypesIds = absenceTypes.map(function (absenceType) {
              return absenceType.id;
            });

            absenceTypesAndIds = {
              types: absenceTypes,
              ids: absenceTypesIds
            };

            return absenceTypesAndIds;
          })
          .then(setAbsenceTypesFromEntitlements);
      }

      /**
       * Initializes leave request day types
       *
       * @return {Promise}
       */
      function loadDayTypes() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
          .then(function (dayTypes) {
            vm.leaveRequestDayTypes = dayTypes;
          });
      }

      /**
       * Initializes leave request documents types required for submission
       *
       * @return {Promise}
       */
      function loadDocuments() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_required_document')
          .then(function (documentTypes) {
            vm.sicknessDocumentTypes = documentTypes;
          });
      }

      /**
       * Initializes leave request reasons and indexes them by name like accident etc.,
       *
       * @return {Promise}
       */
      function loadReasons() {
        return OptionGroup.valuesOf('hrleaveandabsences_sickness_reason')
          .then(function (reasons) {
            vm.sicknessReasons = _.indexBy(reasons, 'name');
          });
      }

      /**
       * Initializes leave request statuses
       *
       * @return {Promise}
       */
      function loadStatuses() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (statuses) {
            vm.leaveRequestStatuses = _.indexBy(statuses, 'name');
          });
      }

      /**
       * Called after successful submission of leave request
       *
       * @param {String} eventName name of the event to emit
       */
      function postSubmit(eventName) {
        $rootScope.$emit(eventName, vm.leaveRequest);
        vm.error = null;
        // close the modal
        vm.ok();
      }

      /**
       * Helper function to reset pagination for balance breakdow
       */
      function rePaginate() {
        vm.pagination.totalItems = vm.balance.change.breakdown.length;
        vm.pagination.filteredbreakdown = vm.balance.change.breakdown;
        vm.pagination.pageChanged();
      }

      /**
       * Resets data in dates, types, balance.
       */
      function reset() {
        vm.uiOptions.fromDate = vm.uiOptions.toDate = null;
        vm.uiOptions.workedDate = null;
        vm.uiOptions.showBalance = false;

        vm.leaveRequest.from_date_type = vm.leaveRequest.to_date_type = null;
        vm.leaveRequest.from_date = vm.leaveRequest.to_date = null;

        vm.balance = {
          closing: 0,
          opening: 0,
          change: {
            amount: 0,
            breakdown: []
          }
        };

        if (vm.isLeaveType('sick')) {
          vm.leaveRequest.reason = null;
        }
      }

      /**
       * Sets entitlements and sets the absences type available for the user.
       * It depends on absenceTypesAndIds to be set to list of absence types and ids
       *
       * @param {Object} that contains all absencetypes and their ids
       * @return {Promise}
       */
      function setAbsenceTypesFromEntitlements() {
        return Entitlement.all({
            contact_id: vm.leaveRequest.contact_id,
            period_id: vm.period.id,
            type_id: {
              IN: absenceTypesAndIds.ids
            }
          }, true) // `true` because we want to use the 'future' balance for calculation
          .then(function (entitlements) {
            // create a list of absence types with a `balance` property
            vm.absenceTypes = filterAbsenceTypes(absenceTypesAndIds.types, entitlements);
          });
      }

      /**
       * Sets dates and types for vm.leaveRequest from UI
       */
      function setDateAndTypes() {
        vm.leaveRequest.from_date = vm.uiOptions.fromDate ? convertDateFormatToServer(vm.uiOptions.fromDate) : null;
        vm.leaveRequest.to_date = vm.uiOptions.toDate ? convertDateFormatToServer(vm.uiOptions.toDate) : null;

        if (vm.uiOptions.multipleDays) {
          vm.uiOptions.showBalance = !!vm.leaveRequest.to_date && !!vm.leaveRequest.from_date;
        } else {
          if (vm.uiOptions.fromDate) {
            vm.uiOptions.toDate = vm.uiOptions.fromDate;
            vm.leaveRequest.to_date = vm.leaveRequest.from_date;
            vm.leaveRequest.to_date_type = vm.leaveRequest.from_date_type;
          }

          vm.uiOptions.showBalance = !!vm.leaveRequest.from_date;
        }
      }

      /**
       * Sets the collection for given day types to sent list of day types,
       * also initializes the day types
       *
       * @param {String} dayType like `from` or `to`
       * @param {Array} listOfDayTypes collection of available day types
       */
      function setDayType(dayType, listOfDayTypes) {
        //will create either of leaveRequestFromDayTypes or leaveRequestToDayTypes key
        var keyForDayTypeCollection = 'leaveRequest' + _.startCase(dayType) + 'DayTypes';

        vm[keyForDayTypeCollection] = listOfDayTypes;
        vm.leaveRequest[dayType + '_date_type'] = vm[keyForDayTypeCollection][0].value;
      }

      /**
       * Checks if leaverequest is managed by given manager id and if yes then set the role
       *
       * @param {String} managerContactId
       * @return {Promise}
       */
      function setManagerRole(managerContactId) {
        return vm.leaveRequest.roleOf({
            id: managerContactId
          })
          .then(function (roleParam) {
            if (roleParam === 'manager') {
              role = 'manager';
            }
          });
      }

      /**
       * Sets leave requestion statuses
       */
      function setStatuses() {
        var allowedStatuses = ['approved', 'more_information_requested', 'cancelled'],
          key, status;

        if (vm.isRole('manager')) {
          //remove current status of leaverequest
          _.remove(allowedStatuses, function (status) {
            return status === getStatusFromValue(vm.leaveRequest.status_id).name;
          });

          //filter vm.leaveRequestStatuses to contain statues relevant for manager to act
          for (key in vm.leaveRequestStatuses) {
            if (!_.includes(allowedStatuses, key)) {
              delete vm.leaveRequestStatuses[key];
            }
          }
        }
      }

      /**
       * Sets the min and max for to date from absence period
       */
      function setMinMax() {
        if (vm.uiOptions.fromDate) {
          vm.uiOptions.date.to.options.minDate = vm.uiOptions.fromDate;

          //also re-set to date if from date is changing and less than to date
          if (vm.uiOptions.toDate && moment(vm.uiOptions.toDate).isBefore(vm.uiOptions.fromDate)) {
            vm.uiOptions.toDate = vm.uiOptions.fromDate;
          }
        } else {
          vm.uiOptions.date.to.options.minDate = convertDateFormatFromServer(vm.period.start_date);
        }

        vm.uiOptions.date.to.options.maxDate = convertDateFormatFromServer(vm.period.end_date);
      }

      /**
       * Updates the leaverequest
       */
      function updateRequest() {
        vm.leaveRequest.update()
          .then(function () {
            if (vm.isRole('manager')) {
              postSubmit('LeaveRequest::updatedByManager');
            } else if (vm.isRole('owner')) {
              postSubmit('LeaveRequest::edit');
            }
          })
          .catch(handleError);
      }

      return vm;
    }
  ]);
});
