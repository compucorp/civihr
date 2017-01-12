define([
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'common/services/api/option-group',
  'common/services/hr-settings',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/public-holiday-model',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (components, _, moment) {
  'use strict';

  components.controller('LeaveRequestPopupCtrl', [
    '$log', '$q', '$rootScope', '$uibModalInstance', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'directiveOptions', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'LeaveRequestInstance', 'PublicHoliday',
    function ($log, $q, $rootScope, $modalInstance, AbsencePeriod, AbsenceType,
      OptionGroup, directiveOptions, Calendar, Entitlement, HR_settings,
      LeaveRequest, LeaveRequestInstance, PublicHoliday
    ) {
      $log.debug('LeaveRequestPopupCtrl');

      var vm = {},
        serverDateFormat = 'YYYY-MM-DD';

      vm.absenceTypes = [];
      vm.calendar = {};
      vm.error = undefined;
      vm.leaveRequestDayTypes = [];
      vm.period = {};
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
        isAdmin: false, //when the dialog is opened by manager or admin
        isEdit: false, //when the dialog is opened by the owner
        isChangeExpanded: false,
        multipleDays: true,
        showDatePickerFrom: false,
        showDatePickerTo: false,
        userDateFormat: HR_settings.DATE_FORMAT,
        selectedStatus: undefined,
        showBalance: false,
        statusLabel: '',
        datePickerOptions: {
          startingDay: 1,
          showWeeks: false
        }
      };

      /**
       * Change handler for change request type like multiple or single. It will
       * reset dates, day types, change balance.
       */
      vm.changeInNoOfDays = function () {
        reset();
        //reinitialize opening balance
        vm.balance.opening = vm.selectedAbsenceType.remainder;
      };

      /**
       * Whenever the absence type changes, update the balance opening.
       * Also the balance change needs to be recalculated, if the `from` and `to`
       * dates have been already selected
       */
      vm.onAbsenceTypeChange = function () {
        vm.leaveRequest.type_id = vm.selectedAbsenceType.id;
        // get the `balance` of the newly selected absence type
        vm.balance.opening = vm.selectedAbsenceType.remainder;

        if (canCalculateChange()) {
          vm.loading.calculateBalanceChange = true;
          vm.calculateBalanceChange().then(function () {
            vm.loading.calculateBalanceChange = false;
          });
        }
      };

      /**
       * This should be called whenever a date has been changed
       *
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @param {String} dayType - set to from if from date is selected else to
       */
      vm.onDateChange = function (date, dayType) {
        dayType = dayType || 'from';

        if (vm.uiOptions.multipleDays) {
          vm.uiOptions.showBalance = !!vm.uiOptions.toDate && !!vm.uiOptions.fromDate;
        } else {
          vm.uiOptions.showBalance = !!vm.uiOptions.fromDate;
        }

        vm.loading[dayType + 'DayTypes'] = true;
        filterLeaveRequestDayTypes(date, dayType)
          .then(function () {
            vm.loading[dayType + 'DayTypes'] = false;
            vm.leaveRequest[dayType + '_date'] = convertDateFormatToServer(vm.uiOptions[dayType + 'Date']);

            if (!vm.uiOptions.multipleDays) {
              vm.uiOptions.toDate = vm.uiOptions.fromDate;
              vm.uiOptions.selectedToType = vm.uiOptions.selectedFromType;
              vm.leaveRequest.to_date = vm.leaveRequest.from_date;
              vm.leaveRequest.to_date_type = vm.leaveRequest.from_date_type;
            }

            vm.loading.calculateBalanceChange = true;
            if (canCalculateChange()) {
              vm.calculateBalanceChange().then(function () {
                vm.loading.calculateBalanceChange = false;
              });
            }
          });
      };

      /**
       * Updates leave request to currently selected status
       *
       */
      vm.onStatusChanged = function () {
        vm.leaveRequest.status_id = vm.uiOptions.selectedStatus.value;
      }

      /**
       * Calculate change in balance, it updates local balance variables
       *
       * @return {Promise}
       */
      vm.calculateBalanceChange = function () {
        setDateAndTypes();

        vm.error = undefined;
        return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange())
          .then(function (balanceChange) {
            if (balanceChange) {
              vm.balance.change = balanceChange;
              //the change is negative so adding it will actually subtract it
              vm.balance.closing = vm.balance.opening + vm.balance.change.amount;
              rePaginate();
            }
          })
          .catch(function (errors) {
            if (errors.error_message)
              vm.error = errors.error_message;
            else {
              vm.error = errors;
            }
          });
      };

      /**
       * Checks if submit button can be enabled for user and returns true if succeeds
       *
       * @returns {Boolean}
       */
      vm.canSubmit = function () {
        var canSubmit = vm.leaveRequest.from_date && vm.leaveRequest.to_date &&
          vm.leaveRequest.to_date_type && vm.leaveRequest.from_date_type;

        if(vm.uiOptions.isAdmin) {
          canSubmit = canSubmit && vm.uiOptions.selectedStatus;
        }
        return canSubmit;
      };

      /**
       * Submits the form, only if the leave request is valid, also emits event
       * to listeners that leaverequest is created
       */
      vm.submit = function () {
        /* current absence type (vm.leaveRequest.type_id) doesn't allow that */
        if (vm.balance.closing < 0 && vm.selectedAbsenceType.allow_overuse == '0') {
          // show an error
          vm.error = 'You are not allowed to apply leave in negative';
          return;
        }

        vm.error = undefined;
        vm.leaveRequest.isValid()
          .then(function () {
            vm.leaveRequest.create()
              .then(function () {
                // refresh the list
                $rootScope.$emit('LeaveRequest::new', vm.leaveRequest);
                vm.error = undefined;
                // close the modal
                vm.ok();
              })
              .catch(function (errors) {
                // show errors
                if (errors.error_message)
                  vm.error = errors.error_message;
                else {
                  vm.error = errors;
                }
              })
          })
          .catch(function (errors) {
            // show errors
            if (errors.error_message)
              vm.error = errors.error_message;
            else {
              vm.error = errors;
            }
          });
      };

      /**
       * dismiss modal on successful creation on submit
       */
      vm.ok = function () {
        //todo handle closure to pass data back to callee
        $modalInstance.close({
          $value: vm.leaveRequest
        });
      };

      /**
       * when user cancels the model dialog
       */
      vm.cancel = function () {
        $modalInstance.dismiss({
          $value: 'cancel'
        });
      };

      /**
       * closes the error alerts if any
       */
      vm.closeAlert = function () {
        vm.error = undefined;
      };

      (function initController() {
console.log('directiveOptions.leaveRequest', directiveOptions.leaveRequest);
        vm.uiOptions.isAdmin = directiveOptions.leaveRequest != undefined;

        if (vm.uiOptions.isAdmin) {
          vm.leaveRequest = directiveOptions.leaveRequest;
        }
        else {
          // Create an empty leave request
          vm.leaveRequest = LeaveRequestInstance.init({
            contact_id: directiveOptions.contactId //resolved from directive
          }, false);
        }

        //check if viewed by manager
        isManagedBy(directiveOptions.contactId);

        vm.loading.absenceTypes = true;
        AbsencePeriod.current()
          .then(function (apInstance) {
            vm.period = apInstance;
          })
          .then(function () {
            return initAbsenceTypesAndEntitlements();
          })
          .then(function () {
            initAbsenceType();
            vm.loading.absenceTypes = false;
          })
          .then(function () {
            return initDayTypesAndStatus();
          })
          .then(function () {
            initDates();
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
        var deferred = $q.defer();

        if (!date) {
          deferred.reject([]);
        }

        // Make a copy of the list
        var listToReturn = vm.leaveRequestDayTypes.slice(0);
        date = convertDateFormatToServer(date);

        PublicHoliday.isPublicHoliday(date)
          .then(function (result) {
            if (result) {
              listToReturn = listToReturn.filter(function (publicHoliday) {
                return publicHoliday.name === 'public_holiday';
              });
            } else {
              var inCalendarList = getDayTypesFromDate(date);

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
       * Initializes values for absence types and entitlements when the
       * leave request popup model is displayed
       *
       * @returns {Promise}
       */
      function initAbsenceTypesAndEntitlements() {
        // Fetch all the absence types, except for the sickness ones
        return AbsenceType.all({
            is_sickness: false
          })
          .then(function (absenceTypes) {
            var absenceTypesIds = absenceTypes.map(function (absenceType) {
              return absenceType.id;
            });

            // And then for each of them get the remaining balance from the
            // entitlements linked to them
            return Entitlement.all({
              contact_id: vm.leaveRequest.contact_id,
              period_id: vm.period.id,
              type_id: { in: absenceTypesIds }
            }, true) // `true` because we want to use the 'future' balance for calculation
              .then(function (entitlements) {
                // create a list of absence types with a `balance` property
                vm.absenceTypes = filterAbsenceTypes(absenceTypes, entitlements);
              });
          });
      }

      /**
       * Filters absence type and formats data to be compatible with angular select directives
       *
       * @param {Array} absenceTypes
       * @param {Object} entitlements
       * @returns {Array} of filtered absence types for given entitlements
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
       * Initializes values for work patterns, day types and statuses when the model is loaded
       *
       * @returns {Promise}
       */
      function initDayTypesAndStatus() {
        // Fetch the full calendar for the current user and the current period
        return Calendar.get(vm.leaveRequest.contact_id, vm.period.id)
          .then(function (usersCalendar) {
            vm.calendar = usersCalendar;
          })
          .then(function () {
            // Fetch the leave request day types
            return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
              .then(function (optionValues) {
                vm.leaveRequestDayTypes = optionValues;
              });
          })
          .then(function () {
            return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
              .then(function (optionValues) {
                vm.leaveRequestStatuses = optionValues;
              });
          });
      }

      /**
       * helper function to reset pagination for balance breakdow
       *
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
       * @return {object}
       */
      function valueOfRequestStatus(value) {
        var collection = vm.leaveRequestStatuses,
          key = 'name';
        var specificObject = _.find(collection, function (collectionItem) {
          return collectionItem[key] === value;
        });
        return specificObject[key];
      }

      /**
       * Pick a specific leave request day type
       *
       * @param {string} value - The leave request day type value to match
       * @return {object}
       */
      function getStatusFromValue(value) {
        var collection = vm.leaveRequestStatuses,
          key = 'value';
        return _.find(collection, function (collectionItem) {
          console.log(collectionItem[key], value);
          return collectionItem[key] === value;
        });
      }

      /**
       * Pick a specific leave request day type
       *
       * @param {string} value - The leave request day type value to match
       * @return {object}
       */
      function getDateTypeFromValue(value) {
        var collection = vm.leaveRequestDayTypes,
          key = 'value';
        return _.find(collection, function (collectionItem) {
          return collectionItem[key] === value;
        });
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
       * @param {Date} date
       * @return {Date} Javascript date
       */
      function convertDateFormatFromServer(date) {
        return moment(date, serverDateFormat).toDate();
      }

      /**
       * Resets data in dates, types, balance.
       */
      function reset() {
        vm.uiOptions.fromDate = vm.uiOptions.toDate = undefined;
        vm.uiOptions.selectedFromType = vm.uiOptions.selectedToType = undefined;
        vm.uiOptions.showBalance = false;

        vm.leaveRequest.from_date_type = vm.leaveRequest.to_date_type = undefined;
        vm.leaveRequest.from_date = vm.leaveRequest.to_date = undefined;

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
       * heler function to obtain params for leave request calculateBalanceChange api call
       *
       * @returns {Object} containing required keys for leave request
       */
      function getParamsForBalanceChange() {
        var params = _.pick(vm.leaveRequest, ['contact_id', 'from_date', 'from_date_type', 'to_date', 'to_date_type']);

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
       * sets dates and types for vm.leaveRequest from UI
       */
      function setDateAndTypes() {
        if (vm.uiOptions.selectedToType) {
          vm.leaveRequest.to_date_type = vm.uiOptions.selectedToType.name;
        }

        if (vm.uiOptions.selectedFromType) {
          vm.leaveRequest.from_date_type = vm.uiOptions.selectedFromType.name;

          if (!vm.uiOptions.multipleDays) {
            vm.leaveRequest.to_date_type = vm.leaveRequest.from_date_type;
          }
        }

        vm.leaveRequest.from_date = convertDateFormatToServer(vm.uiOptions.fromDate);
        vm.leaveRequest.to_date = convertDateFormatToServer(vm.uiOptions.toDate);
      }

      /**
       * gets list of day types if its found to be weekend or non working in calendar
       *
       * @param {Date} date to Checks
       * @returns {Array} non-empty if found else empty array
       */
      function getDayTypesFromDate(date) {
        var listToReturn = [];

        try {
          if (vm.calendar.isNonWorkingDay(date)) {
            listToReturn = listToReturn.filter(function (day) {
              return day.name === 'non_working_day';
            });
          } else if (vm.calendar.isWeekend(date)) {
            listToReturn = listToReturn.filter(function (day) {
              return day.name === 'weekend';
            });
          }
        } catch (e) {
          listToReturn = [];
        }

        return listToReturn;
      }

      /**
       * sets the collection for given day types to sent list of day types,
       * also initializes the day types
       *
       * @param {String} dayType like `from` or `to`
       * @param {Array} listOfDayTypes collection of available day types
       */
      function setDayType(dayType, listOfDayTypes) {
        vm['leaveRequest' + _.startCase(dayType) + 'DayTypes'] = listOfDayTypes;
        vm.uiOptions['selected' + _.startCase(dayType) + 'Type'] = vm['leaveRequest' + _.startCase(dayType) + 'DayTypes'][0];
        vm.leaveRequest[dayType + '_date_type'] = vm.uiOptions['selected' + _.startCase(dayType) + 'Type'].name;
      }

      /**
       * checks if all params are set to calculate balance
       *
       * @param {Array} listOfDayTypes collection of available day types
       */
      function canCalculateChange() {
        return vm.leaveRequest.from_date && vm.leaveRequest.to_date &&
          vm.leaveRequest.from_date_type && vm.leaveRequest.to_date_type;
      }

      function isManagedBy(managerContactId) {
        vm.leaveRequest.roleOf({id : managerContactId})
          .then(function (role) {
            if(role === 'manager') {
              vm.uiOptions.isAdmin = true;
            }
          });
      }

      function initAbsenceType() {
        if(vm.uiOptions.isAdmin) {
          vm.selectedAbsenceType = _.find(vm.absenceTypes, function (absenceType) {
            return absenceType.id == vm.leaveRequest.type_id;
          });
        }
        else{
        // Assign the first absence type to the leave request
          vm.selectedAbsenceType = vm.absenceTypes[0];
        }

        //vm.selectedAbsenceType = initAbsenceType();
        vm.leaveRequest.type_id = vm.selectedAbsenceType.id;
        // Init the `balance` object based on the first absence type
        vm.balance.opening = vm.selectedAbsenceType.remainder;
      }

      function initDates() {
        initStatus();
        initDayTypes();
        if(vm.uiOptions.isAdmin) {
          vm.uiOptions.fromDate = convertDateFormatFromServer(vm.leaveRequest.from_date);
          vm.onDateChange(vm.uiOptions.fromDate, 'from');
          vm.uiOptions.toDate = convertDateFormatFromServer(vm.leaveRequest.to_date);
          vm.onDateChange(vm.uiOptions.fromDate, 'to');
        }
      }

      function initDayTypes() {
        if(vm.uiOptions.isAdmin) {
          vm.uiOptions.selectedFromType = getDateTypeFromValue(vm.leaveRequest.from_date_type);
          vm.uiOptions.selectedToType = getDateTypeFromValue(vm.leaveRequest.to_date_type);
        }
        initBalanceChange();
      }

      function initBalanceChange() {
        if(vm.uiOptions.isAdmin) {
          vm.uiOptions.showBalance = true;
          if (canCalculateChange()) {
            vm.loading.calculateBalanceChange = true;
            vm.calculateBalanceChange().then(function () {
              vm.loading.calculateBalanceChange = false;
            });
          }
        }
      }

      function initStatus() {
        if(vm.uiOptions.isAdmin) {
          vm.uiOptions.statusLabel = getStatusFromValue(vm.leaveRequest.status_id).label;
          //waiting_approval is removed below so call the above before it
          setStatusesForManager();
          //vm.uiOptions.selectedStatus = getStatusFromValue(vm.leaveRequest.status_id);
        }
        else{
          vm.leaveRequest.status_id = valueOfRequestStatus('waiting_approval');
        }
      }

      function setStatusesForManager() {
        if(vm.uiOptions.isAdmin) {
          vm.leaveRequestStatuses = vm.leaveRequestStatuses.filter(function (status){
            return status.name === 'approved' || status.name === 'more_information_requested' || status.name === 'cancelled';
          });
        }
      }

      return vm;
    }
  ]);
});
