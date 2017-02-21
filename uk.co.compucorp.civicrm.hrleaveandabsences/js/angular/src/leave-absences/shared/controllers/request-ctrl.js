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
], function (controllers, _, moment) {
  'use strict';

  controllers.controller('RequestCtrl', [
    '$log', '$q', '$rootScope', 'Contact', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'PublicHoliday', 'shared-settings',
    function ($log, $q, $rootScope, Contact, AbsencePeriod, AbsenceType,
      OptionGroup, Calendar, Entitlement, HR_settings,
      LeaveRequest, PublicHoliday, sharedSettings
    ) {
      $log.debug('RequestCtrl');

      var absenceTypesAndIds,
        initialLeaveRequestAttributes = {}, //used to compare the change in leaverequest in edit mode
        mode = '', //can be edit, create, view
        role = '', //could be manager, owner or admin
        selectedAbsenceType = {},
        vm = {};

      vm.absencePeriods = [];
      vm.absenceTypes = [];
      vm.calendar = {};
      vm.contact = {};
      vm.error = null;
      vm.requestDayTypes = [];
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
        this._reset();
        //reinitialize opening balance
        initAbsenceType.call(this);
      };

      /**
       * When user cancels the model dialog
       */
      vm.cancel = function () {
        this.$modalInstance.dismiss({
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
        var that = this;

        setDateAndTypes.call(that);

        if (!canCalculateChange.call(that)) {
          return $q.resolve();
        }

        vm.error = null;
        that.loading.calculateBalanceChange = true;
        return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange.call(that))
          .then(function (balanceChange) {
            if (balanceChange) {
              that.balance.change = balanceChange;
              //the change is negative so adding it will actually subtract it
              that.balance.closing = that.balance.opening + that.balance.change.amount;
              rePaginate.call(that);
            }
            that.loading.calculateBalanceChange = false;
          })
          .catch(handleError);
      };

      /**
       * Checks if submit button can be enabled for user and returns true if succeeds
       *
       * @return {Boolean}
       */
      vm.canSubmit = function () {
        var canSubmit = canCalculateChange.call(this);

        //check if user has changed any attribute
        if (vm.isMode('edit')) {
          canSubmit = canSubmit && !_.isEqual(initialLeaveRequestAttributes, this.request.attributes());
        }

        //check if manager has changed status
        if (vm.isRole('manager') && vm.requestStatuses) {
          //waiting_approval will not be available in vm.requestStatuses if manager has changed selection
          canSubmit = canSubmit && !!getStatusFromValue(this.request.status_id);
        }

        return canSubmit && !vm.isMode('view');
      };

      /**
       * Checks if popup is opened in given leave type like `leave` or `sick` or 'toil'
       *
       * @param {String} leaveTypeParam to check the leave type of current request
       * @return {Boolean}
       */
      vm.isLeaveType = function (leaveTypeParam) {
        return this.leaveType === leaveTypeParam;
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
        var that = this;

        //todo handle closure to pass data back to callee
        this.$modalInstance.close({
          $value: that.request
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

        // current absence type (this.request.type_id) doesn't allow that
        if (this.balance.closing < 0 && selectedAbsenceType.allow_overuse == '0') {
          // show an error
          vm.error = 'You are not allowed to apply leave in negative';
          return;
        }

        vm.error = null;
        //update leaverequest

        if (canViewOrEdit()) {
          updateRequest.call(this);
        } else {
          createRequest.call(this);
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
        var oldPeriodId = vm.period.id,
          that = this;
        dayType = dayType || 'from';
        vm.loading[dayType + 'DayTypes'] = true;

        return checkAndSetAbsencePeriod.call(that, date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId == vm.period.id;

            if (!isInCurrentPeriod) {
              //partial reset is required when user has selected a to date and
              //then changes absence period from from date
              //no reset required for single days and to date changes
              if (vm.uiOptions.multipleDays && dayType === 'from') {
                vm.uiOptions.showBalance = false;
                vm.uiOptions.toDate = null;
                that.request.to_date = null;
                that.request.to_date_type = null;
              }

              return $q.all([
                loadAbsenceTypes.call(that),
                loadCalendar.call(that)
              ]);
            }
          })
          .then(function () {
            setMinMax.call(that);

            return filterLeaveRequestDayTypes.call(that, date, dayType);
          })
          .then(function () {
            vm.loading[dayType + 'DayTypes'] = false;

            return vm.updateBalance.call(that);
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
        selectedAbsenceType = getSelectedAbsenceType.call(this);
        // get the `balance` of the newly selected absence type
        this.balance.opening = selectedAbsenceType.remainder;

        vm.calculateBalanceChange.call(this);
      };

      /**
       * Initialize request attributes based on directive
       *
       * @return {Object} attributes
       */
      vm._initRequestAttributes = function () {
        var attributes;

        //if set indicates that leaverequest is either being managed or edited
        if (this.directiveOptions.leaveRequest) {
          //get a clone so that it is not the same reference as passed from callee
          attributes = _.cloneDeep(this.directiveOptions.leaveRequest.attributes());
        } else {
          attributes = {
            contact_id: this.directiveOptions.contactId
          };
        }

        return attributes;
      };

      /**
       * Initializes the controller on loading the dialog
       *
       * @return {Promise}
       */
      vm._init = function () {
        var that = this;

        return loadStatuses()
          .then(function () {
            initUserRole.call(that);
            initOpenMode.call(that);
            return loadAbsencePeriods.call(that);
          })
          .then(function () {
            initAbsencePeriod.call(that);
            setMinMax.call(that);

            return $q.all([
              loadAbsenceTypes.call(that),
              loadCalendar.call(that)
            ]);
          })
          .then(function () {
            return loadDayTypes.call(that);
          })
          .then(function () {
            return initDates.call(that);
          })
          .then(function () {
            initAbsenceType.call(that);
            initStatus.call(that);
            initContact.call(that);

            if (vm.isMode('edit')) {
              initialLeaveRequestAttributes = that.request.attributes();
            }
          });
      };

      /**
       * Resets data in dates, types, balance.
       */
      vm._reset = function () {
        vm.uiOptions.fromDate = vm.uiOptions.toDate = null;
        vm.uiOptions.workedDate = null;
        vm.uiOptions.showBalance = false;

        this.request.from_date_type = this.request.to_date_type = null;
        this.request.from_date = this.request.to_date = null;

        this.balance = {
          closing: 0,
          opening: 0,
          change: {
            amount: 0,
            breakdown: []
          }
        };
      };

      /**
       * Checks if all params are set to calculate balance
       *
       * @param {Boolean} true if all present else false
       */
      function canCalculateChange() {
        return !!this.request.from_date && !!this.request.to_date &&
          !!this.request.from_date_type && !!this.request.to_date_type;
      }

      /**
       * Creates leaverequest
       */
      function createRequest() {
        var that = this;

        that.request.isValid()
          .then(function () {
            that.request.create()
              .then(function () {
                // refresh the list
                postSubmit.call(that, 'LeaveRequest::new');
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
          inCalendarList,
          listToReturn,
          that = this;

        if (!date) {
          deferred.reject([]);
        }

        // Make a copy of the list
        listToReturn = that.requestDayTypes.slice(0);

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

            setDayType.call(that, dayType, listToReturn);
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
        return _.pick(this.request, ['contact_id', 'from_date',
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
        var that = this;

        return _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id == that.request.type_id;
        });
      }

      /**
       * Gets status object for given status value
       *
       * @param value of the status
       * @return {Object} option group of type status
       */
      function getStatusFromValue(value) {
        var key, foundStatus, keys = Object.keys(vm.requestStatuses);

        for (key in keys) {
          foundStatus = vm.requestStatuses[keys[key]];
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
        if (this.request.id) {
          mode = 'edit';

          //approved, admin_approved, rejected, cancelled
          var viewModes = [vm.requestStatuses['approved'].value, vm.requestStatuses['admin_approved'].value,
            vm.requestStatuses['rejected'].value, vm.requestStatuses['cancelled'].value
          ];

          if (vm.isRole('owner') && viewModes.indexOf(this.request.status_id) > -1) {
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
        if (this.directiveOptions.leaveRequest &&
          this.directiveOptions.leaveRequest.contact_id != this.directiveOptions.contactId) {
          //check if manager is responding to leave request
          return setManagerRole.call(this, this.directiveOptions.contactId);
        }
        //owner is editing or viewing popup, no api call - direct set
        role = 'owner';
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
          selectedAbsenceType = getSelectedAbsenceType.call(this);
        } else {
          // Assign the first absence type to the leave request
          selectedAbsenceType = vm.absenceTypes[0];
          this.request.type_id = selectedAbsenceType.id;
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
        var deferred = $q.defer(),
          that = this;

        if (canViewOrEdit()) {
          var attributes = this.request.attributes();

          vm.uiOptions.fromDate = convertDateFormatFromServer(this.request.from_date);

          vm.updateAbsencePeriodDatesTypes.call(that, vm.uiOptions.fromDate, 'from')
            .then(function () {
              //to_date and type has been reset in above call so reinitialize from clone
              that.request.to_date = attributes.to_date;
              that.request.to_date_type = attributes.to_date_type;
              vm.uiOptions.toDate = convertDateFormatFromServer(that.request.to_date);
              vm.updateAbsencePeriodDatesTypes.call(that, vm.uiOptions.toDate, 'to')
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
          //set it before vm.requestStatuses gets filtered
          vm.statusLabel = getStatusFromValue(this.request.status_id).label;
          if (vm.isRole('manager')) {
            setStatuses.call(this);
          }
        } else if (vm.isMode('create')) {
          this.request.status_id = vm.requestStatuses['waiting_approval'].value;
        }
      }

      /**
       * Initialize contact
       *
       * {Promise}
       */
      function initContact() {
        if (vm.isRole('manager')) {
          return Contact.find(this.request.contact_id)
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
        return Calendar.get(this.request.contact_id, vm.period.id)
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
        var that = this;

        return AbsenceType.all({
            is_sick: vm.isLeaveType.call(that, 'sick')
          })
          .then(function (absenceTypes) {
            var absenceTypesIds = absenceTypes.map(function (absenceType) {
              return absenceType.id;
            });

            absenceTypesAndIds = {
              types: absenceTypes,
              ids: absenceTypesIds
            };

            return setAbsenceTypesFromEntitlements.call(that, absenceTypesAndIds);
          });
      }

      /**
       * Initializes leave request day types
       *
       * @return {Promise}
       */
      function loadDayTypes() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
          .then(function (dayTypes) {
            vm.requestDayTypes = dayTypes;
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
            vm.requestStatuses = _.indexBy(statuses, 'name');
          });
      }

      /**
       * Called after successful submission of leave request
       *
       * @param {String} eventName name of the event to emit
       */
      function postSubmit(eventName) {
        $rootScope.$emit(eventName, this.request);
        vm.error = null;
        // close the modal
        vm.ok.call(this);
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
       * Sets entitlements and sets the absences type available for the user.
       * It depends on absenceTypesAndIds to be set to list of absence types and ids
       *
       * @param {Object} absenceTypesAndIds contains all absencetypes and their ids
       * @return {Promise}
       */
      function setAbsenceTypesFromEntitlements(absenceTypesAndIds) {
        var that = this;

        return Entitlement.all({
            contact_id: that.request.contact_id,
            period_id: vm.period.id,
            type_id: { IN: absenceTypesAndIds.ids }
          }, true) // `true` because we want to use the 'future' balance for calculation
          .then(function (entitlements) {
            // create a list of absence types with a `balance` property
            vm.absenceTypes = filterAbsenceTypes(absenceTypesAndIds.types, entitlements);
          });
      }

      /**
       * Sets dates and types for vm.request from UI
       */
      function setDateAndTypes() {
        this.request.from_date = vm.uiOptions.fromDate ? convertDateFormatToServer(vm.uiOptions.fromDate) : null;
        this.request.to_date = vm.uiOptions.toDate ? convertDateFormatToServer(vm.uiOptions.toDate) : null;

        if (vm.uiOptions.multipleDays) {
          vm.uiOptions.showBalance = !!this.request.to_date && !!this.request.from_date;
        } else {
          if (vm.uiOptions.fromDate) {
            vm.uiOptions.toDate = vm.uiOptions.fromDate;
            this.request.to_date = this.request.from_date;
            this.request.to_date_type = this.request.from_date_type;
          }

          vm.uiOptions.showBalance = !!this.request.from_date;
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
        var keyForDayTypeCollection = 'request' + _.startCase(dayType) + 'DayTypes';

        this[keyForDayTypeCollection] = listOfDayTypes;
        this.request[dayType + '_date_type'] = this[keyForDayTypeCollection][0].value;
      }

      /**
       * Checks if leaverequest is managed by given manager id and if yes then set the role
       *
       * @param {String} managerContactId
       * @return {Promise}
       */
      function setManagerRole(managerContactId) {
        return this.request.roleOf({
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
          key,
          status,
          that = this;

        if (vm.isRole('manager')) {
          //remove current status of leaverequest
          _.remove(allowedStatuses, function (status) {
            return status === getStatusFromValue(that.request.status_id).name;
          });

          //filter vm.requestStatuses to contain statues relevant for manager to act
          for (key in vm.requestStatuses) {
            if (!_.includes(allowedStatuses, key)) {
              delete vm.requestStatuses[key];
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
        var that = this;

        that.request.update()
          .then(function () {
            if (vm.isRole('manager')) {
              postSubmit.call(that, 'LeaveRequest::updatedByManager');
            } else if (vm.isRole('owner')) {
              postSubmit.call(that, 'LeaveRequest::edit');
            }
          })
          .catch(handleError);
      }

      return vm;
    }
  ]);
});
