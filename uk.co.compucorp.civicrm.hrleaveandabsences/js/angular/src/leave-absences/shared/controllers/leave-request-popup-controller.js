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
], function (components, _, moment) {
  'use strict';

  components.controller('LeaveRequestPopupCtrl', [
    '$log', '$q', '$rootScope', '$uibModalInstance', 'Contact', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'directiveOptions', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'LeaveRequestInstance', 'PublicHoliday',
    function ($log, $q, $rootScope, $modalInstance, Contact, AbsencePeriod, AbsenceType,
      OptionGroup, directiveOptions, Calendar, Entitlement, HR_settings,
      LeaveRequest, LeaveRequestInstance, PublicHoliday
    ) {
      $log.debug('LeaveRequestPopupCtrl');

      var absenceTypesAndIds,
        initialLeaveRequest = {}, //used to compare the change in leaverequest in edit mode
        selectedAbsenceType = {},
        serverDateFormat = 'YYYY-MM-DD',
        vm = {};

      vm.absencePeriods = [];
      vm.absenceTypes = [];
      vm.calendar = {};
      vm.contact = {};
      vm.error = null;
      vm.leaveRequestDayTypes = [];
      vm.mode = ''; //can be edit, create, view
      vm.period = {};
      vm.role = ''; //could be manager, owner or admin
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
          }
        }
      };

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
       * This should be called whenever a date has been changed
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @param {String} dayType - set to from if from date is selected else to
       * @return {Promise}
       */
      vm.onDateChange = function (date, dayType) {
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

              return $q.all([setAbsenceTypesFromEntitlements(), loadCalendar()]);
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
       * Calculate change in balance, it updates local balance variables.
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      vm.calculateBalanceChange = function () {
        setDateAndTypes();

        if (!canCalculateChange()) {
          return $q.resolve({});
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
        if (vm.mode == 'edit') {
          canSubmit = canSubmit &&  !_.isEqual(initialLeaveRequest, vm.leaveRequest.attributes());
        }

        //check if manager has changed status
        if (vm.role === 'manager' && vm.leaveRequestStatuses) {
          //waiting_approval will not be available in vm.leaveRequestStatuses if manager has changed selection
          canSubmit = canSubmit && !!vm.leaveRequestStatuses[vm.leaveRequest.status_id];
        }

        return canSubmit;
      };

      /**
       * Submits the form, only if the leave request is valid, also emits event
       * to listeners that leaverequest is created.
       * Also, checks if its an update request from manager and accordingly updates leave request
       */
      vm.submit = function () {
        // current absence type (vm.leaveRequest.type_id) doesn't allow that
        if (vm.balance.closing < 0 && selectedAbsenceType.allow_overuse == '0') {
          // show an error
          vm.error = 'You are not allowed to apply leave in negative';
          return;
        }

        vm.error = null;
        //update leaverequest

        if (canEdit()) {
          updateRequest();
        } else {
          createRequest();
        }
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
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;

        initLeaveRequest()
          .then(function () {
            return $q.all[initUserRole(), initOpenMode()];
          })
          .then(function () {
            return loadAbsencePeriods();
          })
          .then(function () {
            initAbsencePeriod();
            setMinMax();

            return $q.all([loadAbsenceTypes(), loadCalendar()]);
          })
          .then(function () {
            return $q.all([loadDayTypes(), loadStatuses()]);
          })
          .then(function () {
            initAbsenceType();
            initStatus();
            initContact();

            //the promise will set the day types so that initial value is set properly
            return initDates();
          })
          .then( function () {
            if(vm.mode == 'edit') {
              initialLeaveRequest = _.cloneDeep(vm.leaveRequest.attributes());
            }
          })
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();


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
       * Initialize open mode of the dialog
       *
       * @return {Promise}
       */
      function initOpenMode() {
        if (vm.leaveRequest.id) {
          vm.mode = 'edit';
          //todo in future
          //if owner and status is approved then view only mode
          // if(vm.role === 'owner' && vm.leaveRequest.status_id == valueOfRequestStatus('approved')){
          //   vm.mode = 'view';
          // }
        } else {
          vm.mode = 'create';
        }
        return $q.resolve(vm.mode);
      }

      /**
       * Initialize user's role
       *
       * @return {Promise}
       */
      function initUserRole() {
        if (directiveOptions.leaveRequest &&
          directiveOptions.leaveRequest.contact_id != directiveOptions.contactId) {
          //check if manager is responding to leave request
          return setManagerRole(directiveOptions.contactId);
        }
        //owner is editing or viewing popup, no api call - direct set
        vm.role = 'owner';
        return $q.resolve(vm.role);
      }

      /**
       * Initialize leaverequest based on attributes that come from directive
       *
       * @return {Promise}
       */
      function initLeaveRequest() {
        //if set indicates that leaverequest is either being managed or edited
        if (directiveOptions.leaveRequest) {
          //get a clone so that it is not the same reference as passed from callee
          var cloneAttributes = _.cloneDeep(directiveOptions.leaveRequest.attributes());

          //init to get methods like roleOf again on leaverequest instance as cloning removes them
          vm.leaveRequest = LeaveRequestInstance.init(cloneAttributes);
        } else {
          vm.leaveRequest = LeaveRequestInstance.init({
            contact_id: directiveOptions.contactId //resolved from directive
          });
        }

        return $q.resolve(vm.leaveRequest);
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
        // Fetch all the absence types, except for the sickness ones
        return AbsenceType.all({
            is_sick: false
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
       * Initializes user's calendar (work patterns)
       *
       * @return {Promise}
       */
      function loadCalendar() {
        return Calendar.get(vm.leaveRequest.contact_id, vm.period.id)
          .then(function (usersCalendar) {
            vm.calendar = usersCalendar;
          })
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
       * Initializes leave request statuses
       *
       * @return {Promise}
       */
      function loadStatuses() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (statuses) {
            vm.leaveRequestStatuses = _.indexBy(statuses, 'value');
          });
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
       * Pick a specific value out of a leave request statuses
       *
       * @param {string} value - The leave request status value to match
       * @return {String}
       */
      function valueOfRequestStatus(value) {
        return _.find(vm.leaveRequestStatuses, function (status) {
          return status['name'] === value;
        })['name'];
      }

      /**
       * Converts given date to server format
       *
       * @param {Date} date
       * @return {Date} converted to server format
       */
      function convertDateFormatToServer(date) {
        return moment(date).format(serverDateFormat);
      }

      /**
       * Converts given date to javascript date as expected by uib-datepicker
       *
       * @param {Date/String} date from server
       * @return {Date} Javascript date
       */
      function convertDateFormatFromServer(date) {
        return moment(date, serverDateFormat).clone().toDate();
      }

      /**
       * Resets data in dates, types, balance.
       */
      function reset() {
        vm.uiOptions.fromDate = vm.uiOptions.toDate = null;
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
      }

      /**
       * Helper function to obtain params for leave request calculateBalanceChange api call
       *
       * @return {Object} containing required keys for leave request
       */
      function getParamsForBalanceChange() {
        var params = _.pick(vm.leaveRequest, ['contact_id', 'from_date',
          'from_date_type', 'to_date', 'to_date_type'
        ]);

        //todo to remove in future when this call is consistent with leaverequest db fields name
        return _.mapKeys(params, function (value, key) {
          if (key == 'from_date_type') {
            return 'from_type';
          } else if (key == 'to_date_type') {
            return 'to_type';
          }

          return key;
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
       * Gets list of day types if its found to be weekend or non working in calendar
       *
       * @param {Date} date to Checks
       * @param {Array} listOfDayTypes array of day types
       * @return {Array} non-empty if found else empty array
       */
      function getDayTypesFromDate(date, listOfDayTypes) {
        var listToReturn = [];

        try {
          if (vm.calendar.isNonWorkingDay(date)) {
            listToReturn = listOfDayTypes.filter(function (day) {
              return day.name === 'non_working_day';
            });
          } else if (vm.calendar.isWeekend(date)) {
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
        vm.leaveRequest[dayType + '_date_type'] = vm[keyForDayTypeCollection][0].name;
      }

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
       * Checks if leaverequest is managed by given manager id and if yes then set the role
       *
       * @param {String} managerContactId
       * @return {Promise}
       */
      function setManagerRole(managerContactId) {
        return vm.leaveRequest.roleOf({
            id: managerContactId
          })
          .then(function (role) {
            if (role === 'manager') {
              vm.role = 'manager';
            }
          });
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
       * Initialize absence types
       */
      function initAbsenceType() {
        if (canEdit()) {
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
       * Initialize from and to dates, day types and contact
       *
       * @return {Promise}
       */
      function initDates() {
        var deferred = $q.defer();

        if (canEdit()) {
          var attributes = _.cloneDeep(vm.leaveRequest.attributes());

          vm.uiOptions.fromDate = convertDateFormatFromServer(vm.leaveRequest.from_date);

          vm.onDateChange(vm.uiOptions.fromDate, 'from')
            .then(function () {
              //to_date and type has been reset in above call so reinitialize from clone
              vm.leaveRequest.to_date = attributes.to_date;
              vm.leaveRequest.to_date_type = attributes.to_date_type;
              vm.uiOptions.toDate = convertDateFormatFromServer(vm.leaveRequest.to_date);
              vm.onDateChange(vm.uiOptions.toDate, 'to')
                .then( function () {
                  //reolve only after both from and to day types are also set
                  deferred.resolve({});
                });
            });
        }
        else {
          deferred.resolve({});
        }

        return deferred.promise;
      }

      /**
       * Initialize status
       */
      function initStatus() {
        if (canEdit()) {
          //set it before vm.leaveRequestStatuses gets filtered
          vm.statusLabel = vm.leaveRequestStatuses[vm.leaveRequest.status_id].label;
          if (vm.role === 'manager') {
            setStatuses();
          }
        } else {
          vm.leaveRequest.status_id = valueOfRequestStatus('waiting_approval');
        }
      }

      /**
       * Initialize contact
       *
       * {Promise}
       */
      function initContact() {
        if (vm.role === 'manager') {
          return Contact.find(vm.leaveRequest.contact_id)
            .then(function (contact) {
              vm.contact = contact;
            });
        }

        return $q.resolve({});
      }

      /**
       * Sets leave requestion statuses
       */
      function setStatuses() {
        var allowedStatuses = ['approved', 'more_information_requested', 'cancelled'],
          key, status;

        if (vm.role === 'manager') {
          //remove current status of leaverequest
          _.remove(allowedStatuses, function (status) {
            return status === vm.leaveRequestStatuses[vm.leaveRequest.status_id].name;
          });

          //filter vm.leaveRequestStatuses to contain statues relevant for manager to act
          for (key in vm.leaveRequestStatuses) {
            status = vm.leaveRequestStatuses[key];

            if (!_.includes(allowedStatuses, status.name)) {
              delete vm.leaveRequestStatuses[key];
            }
          }
        }
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
       * Updates the leaverequest
       */
      function updateRequest() {
        vm.leaveRequest.update()
          .then(function () {
            if (vm.role == 'manager') {
              postSubmit('LeaveRequest::updatedByManager');
            }
            else if (vm.role == 'owner') {
              postSubmit('LeaveRequest::edit');
            }

          })
          .catch(handleError);
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
       * Checks if user can view or edit leaverequest
       *
       * @return {Boolean}
       */
      function canEdit() {
        return vm.mode === 'edit';
      }

      /**
       * Gets currently selected absence type from leave request type_id
       */
      function getSelectedAbsenceType() {
        return _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id == vm.leaveRequest.type_id;
        });
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
        }
        else {
          vm.uiOptions.date.to.options.minDate = convertDateFormatFromServer(vm.period.start_date);
        }

        vm.uiOptions.date.to.options.maxDate = convertDateFormatFromServer(vm.period.end_date);
      }

      return vm;
    }
  ]);
});
