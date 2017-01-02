define([
  'leave-absences/shared/modules/components',
  'common/lodash',
  'common/moment',
  'common/services/api/option-group',
  'common/services/hr-settings',
], function (components, _, moment) {
  'use strict';

  components.controller('LeaveRequestPopupCtrl', [
    '$log', '$q', '$uibModalInstance', 'AbsencePeriod', 'AbsenceType', 'Entitlement',
    'Calendar', 'LeaveRequestInstance', 'LeaveRequest', 'api.optionGroup', 'baseData',
    'PublicHoliday', 'HR_settings',
    function ($log, $q, $modalInstance, AbsencePeriod, AbsenceType, Entitlement,
      Calendar, LeaveRequestInstance, LeaveRequest, OptionGroup, baseData, PublicHoliday,
      HR_settings) {
      $log.debug('LeaveRequestPopupCtrl');

      var vm = this, serverDateFormat = 'YYYY-MM-DD';
      vm.absenceTypes = [];
      vm.calendar = {};
      vm.leaveRequestDayTypes = [];
      vm.balance = {
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        },
        closing: 0
      };
      vm.period = {};
      vm.multipleDays = true;
      vm.pagination = {
        totalItems: vm.balance.change.breakdown.length,
        filteredbreakdown: vm.balance.change.breakdown,
        currentPage: 1,
        numPerPage: 5,
        pageChanged: function(cp){
          console.log('cp', cp);
          //filter items
          var begin = ((this.currentPage - 1) * this.numPerPage)
          , end = begin + this.numPerPage;

          this.filteredbreakdown = vm.balance.change.breakdown.slice(begin, end);
        }
      };
      function rePaginate(){
        vm.pagination.totalItems = vm.balance.change.breakdown.length;
        vm.pagination.filteredbreakdown = vm.balance.change.breakdown;
        vm.pagination.pageChanged();
      }


      vm.leaveRequestUIOptions = {
        fromDate: new Date(),
        toDate: new Date(),
        showDatePickerFrom: false,
        showDatePickerTo: false,
        isChangeExpanded: false,
        datePickerOptions: {
          startingDay: 1,
          showWeeks: false
        },
        isAdmin: false,
        //dateFormat: 'yyyy-MM-dd',
        userDateFormat: HR_settings.DATE_FORMAT
      };
      // Create an empty leave request
      vm.leaveRequest = LeaveRequestInstance.init({
        from_date: convertDateFormatToServer(vm.leaveRequestUIOptions.fromDate),
        to_date: convertDateFormatToServer(vm.leaveRequestUIOptions.toDate),
        contact_id: baseData.contactId //resolved from directive
      }, false);


      vm.ok = function () {
        vm.close({
          $value: vm.leaveRequest
        });
      };

      vm.cancel = function () {
        $modalInstance.dismiss({
          $value: 'cancel'
        });
      };

      AbsencePeriod.current().then(function (apInstance) {
        vm.period = apInstance;
        vm.initModal();
      });

      vm.initModal = function () {
        // Fetch the full calendar for the current user and the current period
        Calendar.get(vm.leaveRequest.contact_id, vm.period.id)
          .then(function (usersCalendar) {
            console.log('usersCalendar', usersCalendar);
            vm.calendar = usersCalendar;
          });

        // Fetch the leave request day types (All day, 1/2AM, 1/2PM, etc)
        OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
          .then(function (optionValues) {
            vm.leaveRequestDayTypes = optionValues;
            vm.leaveRequestFromDayTypes = vm.filterLeaveRequestDayTypes(vm.leaveRequestUIOptions.fromDate, true);
            vm.leaveRequestToDayTypes = vm.filterLeaveRequestDayTypes(vm.leaveRequestUIOptions.toDate, false);

            //initialize till callback returns
            vm.leaveRequest.from_type = getSpecificValueFromCollection(vm.leaveRequestDayTypes, 'name', 'half_day_am');
            vm.leaveRequest.to_type = getSpecificValueFromCollection(vm.leaveRequestDayTypes, 'name', 'half_day_am');
          });

        OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (optionValues) {
            vm.leaveRequestStatuses = optionValues;
            vm.leaveRequest.status_id = getSpecificValueFromCollection(vm.leaveRequestStatuses, 'name', 'waiting_approval');
          });

        // Fetch all the absence types, except for the sickness ones
        AbsenceType.all({
            is_sickness: false
          })
          .then(function (absenceTypes) {

            var absenceTypesIds = absenceTypes.map(function (item) {
              return item.id;
            })

            // And then for each of them get the remaining balance from the
            // entitlements linked to them
            Entitlement.all({
                contact_id: vm.leaveRequest.contact_id,
                period_id: vm.period.id,
                type_id: { in: absenceTypesIds
                }
              }, true) // `true` because we want to use the "future" balance for calculation
              .then(function (entitlements) {
                console.log('entitlements.length', entitlements);
                // create a list of absence types with a `balance` property
                vm.absenceTypes = entitlements.map(function (entitlementItem) {
                  var absenceType = absenceTypes.find(function (absenceTypeItem) {
                    return absenceTypeItem.id === entitlementItem.type_id;
                  });

                  return {
                    id: entitlementItem.type_id,
                    title: absenceType.title + ' ( ' + entitlementItem.remainder.current + ' ) ',
                    remainder: entitlementItem.remainder.current
                  };
                });

                // Assign the first absence type to the leave request
                vm.selectedAbsenceType = vm.absenceTypes[0];
                vm.leaveRequest.type_id = vm.selectedAbsenceType.id;

                // Init the `balance` object based on the first absence type
                vm.balance.opening = vm.absenceTypes[0].remainder;
                //calculate default for balance change
                calculateBalanceChange();
              });
          });
      }

      /**
       * Whenever the absence type changes, update the balance opening
       * Also the balance change needs to be recalculated, if the `from` and `to`
       * dates have been already selected
       */
      vm.onAbsenceTypeChange = function () {
        // get the `balance` of the newly selected absence type (vm.leaveRequest.type_id)
        vm.balance.opening = vm.selectedAbsenceType.remainder;

        if (vm.leaveRequest.from_date && vm.leaveRequest.to_date) {
          calculateBalanceChange();
        }
      }

      function calculateBalanceChange() {
        if(vm.leaveRequestUIOptions.selectedFromType)
          vm.leaveRequest.from_type = vm.leaveRequestUIOptions.selectedFromType.name;

        if(vm.leaveRequestUIOptions.selectedToType)
          vm.leaveRequest.to_type = vm.leaveRequestUIOptions.selectedToType.name;

        vm.leaveRequest.from_date = convertDateFormatToServer(vm.leaveRequestUIOptions.fromDate);
        vm.leaveRequest.to_date = convertDateFormatToServer(vm.leaveRequestUIOptions.toDate);

        var params = _.pick(vm.leaveRequest, ['contact_id', 'from_date', 'from_type', 'to_date', 'to_type']);

        return LeaveRequest.calculateBalanceChange(params)
          .then(function (balanceChange) {
            if(balanceChange){
              vm.balance.change = balanceChange;
              vm.balance.closing = vm.balance.opening - vm.balance.change.amount;
              rePaginate();
            }
            else{
              console.log('balanceChange', balanceChange);
            }
          })
          .catch(function (error) {
            console.log('calculateBalanceChange error', error);
          });
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
      function convertDateFormatToServer(date){
        return moment(date).format(serverDateFormat);
      }

      /**
       * This method will be used on the view to return a list of available
       * leave request day types (1/2 PM, Non working day, etc) for the given date
       * (which is the date selected by the user via datepicker)
       *
       * If no date is passed, then no list is returned
       *
       *
       * @param  {Date} dateParam
       * @return {Array}
       */
      vm.filterLeaveRequestDayTypes = function (dateParam, isFrom) {
        if (!dateParam) {
          return [];
        }

        var date = convertDateFormatToServer(dateParam);

        // Make a copy of the list
        var listToReturn = vm.leaveRequestDayTypes.slice(0);

        PublicHoliday.isPublicHoliday(date).then(function(result){
          console.log(result, isFrom);
          if(result){
            listToReturn = listToReturn.filter(function(item){
              return item.name === 'public_holiday';
            });
          }
          else{
            if (vm.calendar.isNonWorkingDay(date)) {
              // Only "Non Working Day" option
              listToReturn = listToReturn.filter(function(item){
                return item.name === 'non_working_day';
              });
            } else if (vm.calendar.isWeekend(date)) {
              // Only "Weekend" option
              listToReturn = listToReturn.filter(function(item){
                return item.name === 'weekend';
              });
            } else {
              // "All day", "1/2 AM", and "1/2 PM" options
              listToReturn = listToReturn.filter(function(item){
                return item.name === 'all_day' || item.name === 'half_day_am' || item.name === 'half_day_pm';
              });
            }
          }

          if (isFrom) {
            vm.leaveRequestFromDayTypes = listToReturn;
            vm.leaveRequestUIOptions.selectedFromType = vm.leaveRequestFromDayTypes[0];
          } else {
            vm.leaveRequestToDayTypes = listToReturn;
            vm.leaveRequestUIOptions.selectedToType = vm.leaveRequestToDayTypes[0];
          }
        });

        return listToReturn;
      }

      /**
       * This should be called whenever a date has been changed
       * (whether the date itself or the amount type)
       *
       * First it syncs `from` and `to` date, if it's in "single day" mode
       * Then, if all the dates are there, it gets the balance change
       *
       */
      vm.onDateChange = function (date, isFrom) {
        console.log('vm.onDateChange', date);

        vm.filterLeaveRequestDayTypes(date, !!isFrom);

        vm.leaveRequest.to_date = convertDateFormatToServer(vm.leaveRequestUIOptions.toDate);
        vm.leaveRequest.from_date = convertDateFormatToServer(vm.leaveRequestUIOptions.fromDate);

        if (!vm.multipleDays) {
          vm.leaveRequest.to_date = vm.leaveRequest.from_date;
          vm.leaveRequest.to_type = vm.leaveRequest.from_type;
        }

        if (vm.leaveRequest.from_date && vm.leaveRequest.to_date) {
          calculateBalanceChange();
        }
      }

      /**
       * Submits the form, only if the leave request is valid
       */
      vm.submit = function () {
        // if (vm.balance.closing < 0 && /* current absence type (vm.leaveRequest.type_id) doesn't allow that */) {
        //   // show an error
        //   return;
        // }

        vm.leaveRequest.isValid().then(function () {
          vm.leaveRequest.save();
          // close the modal
          // refresh the list
        })
        .catch(function (errors) {
          // show errors
          return
        })
      }

      return vm;
    }
  ])
});
