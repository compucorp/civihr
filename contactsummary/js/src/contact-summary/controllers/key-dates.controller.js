/* eslint-env amd */

define([
  'common/angular',
  'common/moment'
], function (angular, moment) {
  'use strict';

  KeyDatesController.__name = 'KeyDatesController';
  KeyDatesController.$inject = ['$log', 'contractService', 'jobRoleService', 'pubSub'];

  function KeyDatesController ($log, Contract, JobRole, pubSub) {
    $log.debug('Controller: KeyDatesController');

    var vm = this;

    vm.ready = false;
    vm.dates = [];
    vm.activeContracts = 0;
    vm.activeRoles = 0;

    (function init () {
      getContacts();
      pubSub.subscribe('contract-refresh', resetKeyDates);
    }());

    /**
     * Add the contract dates to the timeline
     *
     * @param {Object} contract
     */
    function addContractDates (contract) {
      vm.dates.push({
        title: contract.title + ' (Start)',
        date: contract.start_date,
        future: isDateInFuture(contract.start_date)
      });

      if (contract.end_date) {
        vm.dates.push({
          title: contract.title + ' (End)',
          date: contract.end_date,
          future: isDateInFuture(contract.end_date)
        });
      }
    }

    /**
     * Fetch Contacts from Server
     */
    function getContacts () {
      Contract.get()
        .then(function (response) {
          angular.forEach(response, function (contract) {
            addContractDates(contract);

            if (contract.is_current === '1') {
              vm.activeContracts++;
            }
          });

          return JobRole.get();
        })
        .then(function (response) {
          angular.forEach(response, function (role) {
            var endDate = moment(role.end_date);

            if (!endDate.isValid() || isDateInFuture(endDate)) {
              vm.activeRoles++;
            }
          });
        })
        .finally(function () {
          vm.ready = true;
        });
    }

    /**
     * Checks if a date is in the future
     *
     * @param {string} date
     * @return {boolean}
     */
    function isDateInFuture (date) {
      return moment().diff(date) < 0;
    }

    /**
     * Resets the dates and gets the contacts again
     */
    function resetKeyDates () {
      vm.dates = [];
      getContacts();
    }
  }

  return KeyDatesController;
});
