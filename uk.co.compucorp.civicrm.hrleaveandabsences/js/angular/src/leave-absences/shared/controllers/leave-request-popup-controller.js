define([
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'common/services/api/option-group',
  'common/services/hr-settings',
], function (components, _, moment) {
  'use strict';

  components.controller('LeaveRequestPopupCtrl', [
    '$log', '$q', '$uibModalInstance', 'AbsencePeriod', 'AbsenceType', 'Entitlement',
    'Calendar', 'LeaveRequestInstance', 'LeaveRequest', 'api.optionGroup', 'baseData',
    'PublicHoliday', 'HR_settings', '$rootScope',
    function ($log, $q, $modalInstance, AbsencePeriod, AbsenceType, Entitlement,
      Calendar, LeaveRequestInstance, LeaveRequest, OptionGroup, baseData, PublicHoliday,
      HR_settings, $rootScope) {
      $log.debug('LeaveRequestPopupCtrl');

      var $ctrl = this,
        serverDateFormat = 'YYYY-MM-DD';

      $ctrl.absenceTypes = [];
      $ctrl.calendar = {};
      $ctrl.leaveRequestDayTypes = [];
      $ctrl.balance = {
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        },
        closing: 0
      };
      $ctrl.period = {};
      $ctrl.pagination = {
        totalItems: $ctrl.balance.change.breakdown.length,
        filteredbreakdown: $ctrl.balance.change.breakdown,
        currentPage: 1,
        numPerPage: 5,
        pageChanged: function () {
          //filter breakdowns
          var begin = ((this.currentPage - 1) * this.numPerPage),
            end = begin + this.numPerPage;

          this.filteredbreakdown = $ctrl.balance.change.breakdown.slice(begin, end);
        }
      };
      $ctrl.uiOptions = {
        showDatePickerFrom: false,
        showDatePickerTo: false,
        isChangeExpanded: false,
        datePickerOptions: {
          startingDay: 1,
          showWeeks: false
        },
        isAdmin: false,
        userDateFormat: HR_settings.DATE_FORMAT,
        showBalance: false,
        multipleDays: true
      };
      $ctrl.error = undefined;
      $ctrl.loading = {};

      // Create an empty leave request
      $ctrl.leaveRequest = LeaveRequestInstance.init({
        contact_id: baseData.contactId //resolved from directive
      }, false);

      (function () {
        $ctrl.loading.absenceTypes = true;
        AbsencePeriod.current().then(function (apInstance) {
            $ctrl.period = apInstance;
          })
          .then(function () {
            return initAbsenceTypesAndEntitlements().then(function () {
              $ctrl.loading.absenceTypes = false;
            });
          })
          .then(function () {
            return initDayTypesAndStatus()
          });
      })();

      /**
       * Initializes values for absence types and entitlements when the model is loaded
       *
       * @returns {Promise}
       */
      function initAbsenceTypesAndEntitlements() {
        // Fetch all the absence types, except for the sickness ones
        return AbsenceType.all({
            is_sickness: false
          })
          .then(function (absenceTypes) {
            var absenceTypesIds = absenceTypes.map(function (item) {
              return item.id;
            })

            // And then for each of them get the remaining balance from the
            // entitlements linked to them
            Entitlement.all({
                contact_id: $ctrl.leaveRequest.contact_id,
                period_id: $ctrl.period.id,
                type_id: { in: absenceTypesIds
                }
              }, true) // `true` because we want to use the 'future' balance for calculation
              .then(function (entitlements) {
                // create a list of absence types with a `balance` property
                $ctrl.absenceTypes = entitlements.map(function (entitlementItem) {
                  var absenceType = absenceTypes.find(function (absenceTypeItem) {
                    return absenceTypeItem.id === entitlementItem.type_id;
                  });

                  return {
                    id: entitlementItem.type_id,
                    title: absenceType.title + ' ( ' + entitlementItem.remainder.current + ' ) ',
                    remainder: entitlementItem.remainder.current,
                    allow_overuse: absenceType.allow_overuse
                  };
                });

                // Assign the first absence type to the leave request
                $ctrl.selectedAbsenceType = $ctrl.absenceTypes[0];
                $ctrl.leaveRequest.type_id = $ctrl.selectedAbsenceType.id;
                // Init the `balance` object based on the first absence type
                $ctrl.balance.opening = $ctrl.selectedAbsenceType.remainder;
              });
          });
      }

      /**
       * Initializes values for work patterns, day types and statuses when the model is loaded
       *
       * @returns {Promise}
       */
      function initDayTypesAndStatus() {
        // Fetch the full calendar for the current user and the current period
        return Calendar.get($ctrl.leaveRequest.contact_id, $ctrl.period.id)
          .then(function (usersCalendar) {
            $ctrl.calendar = usersCalendar;
          })
          .then(function () {
            // Fetch the leave request day types (All day, 1/2AM, 1/2PM, etc)
            return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
              .then(function (optionValues) {
                $ctrl.leaveRequestDayTypes = optionValues;
              });
          })
          .then(function () {
            return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
              .then(function (optionValues) {
                $ctrl.leaveRequestStatuses = optionValues;
                $ctrl.leaveRequest.status_id = getSpecificValueFromCollection($ctrl.leaveRequestStatuses, 'name', 'waiting_approval');
              });
          })
      }

      /**
       * Change handler for change request type like multiple or single. It will
       * reset dates, balanes types.
       *
       */
      $ctrl.changeInNoOfDays = function () {
        //reset dates
        $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = undefined;
        //reset balance change
        $ctrl.balance = {
          opening: $ctrl.selectedAbsenceType.remainder,
          change: {
            amount: 0,
            breakdown: []
          },
          closing: 0
        };
        $ctrl.uiOptions.selectedFromType = $ctrl.uiOptions.selectedToType = undefined;

        //reset dates and types in object also
        $ctrl.leaveRequest.from_date_type = $ctrl.leaveRequest.to_date_type = undefined;
        $ctrl.leaveRequest.from_date = $ctrl.leaveRequest.to_date = undefined;
        //hide change balance section
        $ctrl.uiOptions.showBalance = false;
      }

      /**
       * Whenever the absence type changes, update the balance opening.
       * Also the balance change needs to be recalculated, if the `from` and `to`
       * dates have been already selected
       */
      $ctrl.onAbsenceTypeChange = function () {
        $ctrl.leaveRequest.type_id = $ctrl.selectedAbsenceType.id;
        // get the `balance` of the newly selected absence type
        $ctrl.balance.opening = $ctrl.selectedAbsenceType.remainder;

        if ($ctrl.leaveRequest.from_date && $ctrl.leaveRequest.to_date) {
          $ctrl.loading.calculateBalanceChange = true;
          $ctrl.calculateBalanceChange().then(function () {
            $ctrl.loading.calculateBalanceChange = false;
          });
        }
      }

      /**
       * This should be called whenever a date has been changed
       *
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       */
      $ctrl.onDateChange = function (date, isFrom) {
        if ($ctrl.uiOptions.multipleDays) {
          if ($ctrl.uiOptions.toDate && $ctrl.uiOptions.fromDate) {
            $ctrl.uiOptions.showBalance = true;
          }
        } else {
          if ($ctrl.uiOptions.fromDate) {
            $ctrl.uiOptions.showBalance = true;
          }
        }

        $ctrl.filterLeaveRequestDayTypes(date, !!isFrom)
          .then(function () {
            if (isFrom) {
              $ctrl.leaveRequest.from_date = convertDateFormatToServer($ctrl.uiOptions.fromDate);
              $ctrl.loading.fromDayTypes = false;
            } else {
              $ctrl.leaveRequest.to_date = convertDateFormatToServer($ctrl.uiOptions.toDate);
              $ctrl.loading.toDayTypes = false;
            }

            if (!$ctrl.uiOptions.multipleDays) {
              $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate;
              $ctrl.uiOptions.selectedToType = $ctrl.uiOptions.selectedFromType;
              $ctrl.leaveRequest.to_date = $ctrl.leaveRequest.from_date;
              $ctrl.leaveRequest.to_date_type = $ctrl.leaveRequest.from_date_type;
            }

            if (!$ctrl.uiOptions.showBalance) {
              return
            }

            $ctrl.loading.calculateBalanceChange = true;
            if ($ctrl.leaveRequest.from_date && $ctrl.leaveRequest.to_date) {
              $ctrl.calculateBalanceChange().then(function () {
                $ctrl.loading.dateChangesBalance = false;
              });
            }
          });
      }

      /**
       * Calculate change in balance, it updates local balance variables
       *
       * @return {Promise}
       */
      $ctrl.calculateBalanceChange = function () {
        if ($ctrl.uiOptions.selectedToType) {
          $ctrl.leaveRequest.to_date_type = $ctrl.uiOptions.selectedToType.name;
        }

        if ($ctrl.uiOptions.selectedFromType) {
          $ctrl.leaveRequest.from_date_type = $ctrl.uiOptions.selectedFromType.name;

          if (!$ctrl.uiOptions.multipleDays) {
            $ctrl.leaveRequest.to_date_type = $ctrl.leaveRequest.from_date_type;
          }
        }

        $ctrl.leaveRequest.from_date = convertDateFormatToServer($ctrl.uiOptions.fromDate);
        $ctrl.leaveRequest.to_date = convertDateFormatToServer($ctrl.uiOptions.toDate);

        var params = _.pick($ctrl.leaveRequest, ['contact_id', 'from_date', 'from_date_type', 'to_date', 'to_date_type']);

        //todo to remove in future when this call is consistent with leaverequest db fields name
        params = _.mapKeys(params, function (value, key) {
          if (key == 'from_date_type') {
            return 'from_type';
          } else if (key == 'to_date_type') {
            return 'to_type';
          }

          return key;
        });

        $ctrl.error = undefined;
        return LeaveRequest.calculateBalanceChange(params)
          .then(function (balanceChange) {
            if (balanceChange) {
              $ctrl.balance.change = balanceChange;
              //the change is negative so adding it will actually subtract it
              $ctrl.balance.closing = $ctrl.balance.opening + $ctrl.balance.change.amount;
              rePaginate();
            } else {
              console.log('$ctrl.calculateBalanceChange', params, balanceChange);
            }
          })
          .catch(function (errors) {
            if (errors.error_message)
              $ctrl.error = errors.error_message;
            else {
              $ctrl.error = errors;
            }
          });
      }

      /**
       * This method will be used on the view to return a list of available
       * leave request day types (1/2 PM, Non working day, etc) for the given date
       * (which is the date selected by the user via datepicker)
       *
       * If no date is passed, then no list is returned
       *
       * @param  {Date} dateParam
       * @return {Promise} of array
       */
      $ctrl.filterLeaveRequestDayTypes = function (dateParam, isFrom) {
        var deferred = $q.defer();

        if (!dateParam) {
          deferred.reject([]);
        }

        var date = convertDateFormatToServer(dateParam);
        // Make a copy of the list
        var listToReturn = $ctrl.leaveRequestDayTypes.slice(0);

        PublicHoliday.isPublicHoliday(date).then(function (result) {
          if (result) {

            listToReturn = listToReturn.filter(function (item) {
              return item.name === 'public_holiday';
            });
          } else {
            //if not found the calender methods throw exceptions
            var foundInCalendar = false;

            try {
              if ($ctrl.calendar.isNonWorkingDay(date)) {
                // Only 'Non Working Day' option
                listToReturn = listToReturn.filter(function (item) {
                  return item.name === 'non_working_day';
                });
                foundInCalendar = true;
              } else if ($ctrl.calendar.isWeekend(date)) {
                // Only 'Weekend' option
                listToReturn = listToReturn.filter(function (item) {
                  return item.name === 'weekend';
                });
                foundInCalendar = true;
              }
            } catch (e) {
              //empty catch to catch exceptions
            } finally {
              if (!foundInCalendar) {
                // 'All day', '1/2 AM', and '1/2 PM' options
                listToReturn = listToReturn.filter(function (item) {
                  return item.name === 'all_day' || item.name === 'half_day_am' || item.name === 'half_day_pm';
                });
              }
            }
          }

          if (isFrom) {
            $ctrl.leaveRequestFromDayTypes = listToReturn;
            $ctrl.uiOptions.selectedFromType = $ctrl.leaveRequestFromDayTypes[0];
            $ctrl.leaveRequest.from_date_type = $ctrl.uiOptions.selectedFromType.name;
          } else {
            $ctrl.leaveRequestToDayTypes = listToReturn;
            $ctrl.uiOptions.selectedToType = $ctrl.leaveRequestToDayTypes[0];
            $ctrl.leaveRequest.to_date_type = $ctrl.uiOptions.selectedToType.name;
          }

          deferred.resolve(listToReturn);
        });

        return deferred.promise;
      }

      /**
       * helper function to reset pagination for balance breakdow
       *
       **/
      function rePaginate() {
        $ctrl.pagination.totalItems = $ctrl.balance.change.breakdown.length;
        $ctrl.pagination.filteredbreakdown = $ctrl.balance.change.breakdown;
        $ctrl.pagination.pageChanged();
      }

      /**
       * Pick a specific value out of a collection
       *
       * @param {array} the option group collection key
       * @param {string} key - The sub-collection key
       * @param {string} value - The sub-collection key's value to match
       * @return {object}
       */
      function getSpecificValueFromCollection(collection, key, value) {
        var specificObject = _.find(collection, function (item) {
          return item[key] === value;
        });
        return specificObject[key];
      }

      /**
       * Converts given date to server format
       **/
      function convertDateFormatToServer(date) {
        return moment(date).format(serverDateFormat);
      }

      /**
       * Checks if submit button can be enabled for user and returns true if succeeds
       *
       * @returns {Boolean}
       **/
      $ctrl.canSubmit = function () {
        if ($ctrl.leaveRequest.from_date && $ctrl.leaveRequest.to_date &&
          $ctrl.leaveRequest.to_date_type && $ctrl.leaveRequest.from_date_type) {
          return false;
        }
        return true;
      }

      /**
       * Submits the form, only if the leave request is valid, also emits event
       * to listeners that leaverequest is created
       */
      $ctrl.submit = function () {
        /* current absence type ($ctrl.leaveRequest.type_id) doesn't allow that */
        if ($ctrl.balance.closing < 0 && $ctrl.selectedAbsenceType.allow_overuse == '0') {
          // show an error
          $ctrl.error = 'You are not allowed to apply leave in negative';
          return;
        }

        $ctrl.error = undefined;
        $ctrl.leaveRequest.isValid().then(function () {
            $ctrl.leaveRequest.create()
              .then(function () {
                // refresh the list
                $rootScope.$emit('LeaveRequest::new', $ctrl.leaveRequest);
                $ctrl.error = undefined;
                // close the modal
                $ctrl.ok();
              })
              .catch(function (errors) {
                // show errors
                if (errors.error_message)
                  $ctrl.error = errors.error_message;
                else {
                  $ctrl.error = errors;
                }
              })
          })
          .catch(function (errors) {
            // show errors
            if (errors.error_message)
              $ctrl.error = errors.error_message;
            else {
              $ctrl.error = errors;
            }
          })
      }

      /**
       * dismiss modal on successful creation on submit
       */
      $ctrl.ok = function () {
        //todo handle closure to pass data back to callee
        $modalInstance.close({
          $value: $ctrl.leaveRequest
        });
      };

      /**
       * when user cancels the model dialog
       */
      $ctrl.cancel = function () {
        $modalInstance.dismiss({
          $value: 'cancel'
        });
      };

      /**
       * closes the error alerts if any
       */
      $ctrl.closeAlert = function () {
        $ctrl.error = undefined;
      }

      return $ctrl;
    }
  ])
});
