/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment'
], function (angular, _, moment) {
  'use strict';

  KeyDatesController.__name = 'KeyDatesController';
  KeyDatesController.$inject = ['$log', 'contractService', 'jobRoleService', 'pubSub'];

  function KeyDatesController ($log, Contract, JobRole, pubSub) {
    $log.debug('Controller: KeyDatesController');

    var vm = this;

    vm.dates = [];
    vm.activeContracts = 0;
    vm.activeRoles = 0;
    vm.ready = false;

    (function init () {
      getContacts();
      initSubscribers();
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
     * Fetch Contacts and Job Roles from Server
     */
    function getContacts () {
      resetKeyDates();

      Contract.get()
        .then(function (response) {
          if (!_.isEmpty(response)) {
            angular.forEach(response, function (contract) {
              addContractDates(contract);

              if (contract.is_current === '1') {
                vm.activeContracts++;
              }
            });
          }

          return JobRole.get();
        })
        .then(function (response) {
          angular.forEach(response, function (role) {
            var endDate = moment(role.end_date);

            if (role.end_date === undefined || (endDate.isValid() && isDateInFuture(endDate))) {
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
     * Resets activeRoles and activeContracts counter and
     * resets the list of key dates to empty
     */
    function resetKeyDates () {
      vm.activeContracts = 0;
      vm.activeRoles = 0;
      vm.dates = [];
    }

    // Init subscribers
    function initSubscribers () {
      $log.debug('Subcsribers initialiazed!');

      var events = [
        'Contract::created',
        'Contract::updated',
        'JobRole::created',
        'JobRole::updated',
        'JobRole::deleted'
      ];

      events.forEach(function (event) {
        pubSub.subscribe(event, getContacts);
      });

      pubSub.subscribe('Contract::deleted', function (contract) {
        Contract.removeContract(contract);
        getContacts();
      });
    }
  }

  return KeyDatesController;
});
