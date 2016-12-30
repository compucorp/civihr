define([
  'leave-absences/shared/modules/components',
  'common/lodash',
  'common/services/api/option-group',
], function (components, _) {
  'use strict';

  components.controller('LeaveRequestPopupCtrl', [
    '$log', '$q', '$uibModalInstance', 'AbsencePeriod', 'AbsenceType', 'Entitlement',
    'Calendar', 'LeaveRequestInstance', 'LeaveRequest', 'api.optionGroup','baseData',
    function ($log, $q, $modalInstance, AbsencePeriod, AbsenceType, Entitlement,
      Calendar, LeaveRequestInstance, LeaveRequest, OptionGroup, baseData) {
      $log.debug('LeaveRequestPopupCtrl');

      //var $ctrl = this,
      //var vm = {};
      var vm = this;
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

      vm.leaveRequestUIOptions = {
        //fromDate: new Date(),
        //toDate: new Date(),
        showDatePickerFrom: false,
        showDatePickerTo: false,
        isChangeExpanded: true,
        datePickerOptions: {
          startingDay: 1,
          showWeeks: false
        }
      };
      // Create an empty leave request
      vm.leaveRequest = LeaveRequestInstance.init({
        from_date: new Date(),
        to_date: new Date(),
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
          .then(function (c) {
            vm.calendar = c;
          });

        // Fetch the leave request day types (All day, 1/2AM, 1/2PM, etc)
        OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
          .then(function (optionValues) {
            vm.leaveRequestDayTypes = optionValues;
          });
          OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
            .then(function (optionValues) {
              vm.leaveRequestStatuses = optionValues;
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
                // create a list of absence types with a `balance` property
                vm.absenceTypes = entitlements.map(function (entitlementItem) {
                  var absenceType = absenceTypes.find(function(absenceTypeItem){
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
                vm.leaveRequest.status_id = getSpecificValueFromCollection(vm.leaveRequestStatuses, 'name', 'waiting_approval');
                vm.leaveRequest.from_date_type = getSpecificValueFromCollection(vm.leaveRequestDayTypes, 'name', 'half_day_am');
                vm.leaveRequest.to_date_type = getSpecificValueFromCollection(vm.leaveRequestDayTypes, 'name', 'half_day_am');
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
        var params = _.pick(vm.leaveRequest, ['contact_id', 'from_date', 'from_date_type', 'to_date', 'to_date_type']);
        return LeaveRequest.calculateBalanceChange(params)
          .then(function (balanceChange) {
            vm.balance.change = balanceChange;
            vm.balance.closing = vm.balance.opening - vm.balance.change.amount;
          })
          .catch(function(error){
            console.log('calculateBalanceChange',error);
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

      return vm;
    }
  ])
});
