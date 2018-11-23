/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment'
], function (angular, _, moment) {
  'use strict';

  KeyDatesController.$inject = ['$log', '$q', 'contractService', 'jobRoleService', 'pubSub'];

  function KeyDatesController ($log, $q, Contract, JobRole, pubSub) {
    $log.debug('Controller: KeyDatesController');

    var vm = this;

    vm.dates = [];
    vm.activeContracts = 0;
    vm.activeRoles = 0;
    vm.ready = false;

    vm.$onInit = $onInit;

    function $onInit () {
      loadData();
      initSubscribers();
    }

    /**
     * Add the contract dates to the timeline
     *
     * @param {Object} contract
     */
    function addContractDates (contract) {
      vm.dates.push({
        title: contract.title + ' (Start)',
        date: contract.start_date,
        future: isDateNotInPast(contract.start_date)
      });

      if (contract.end_date) {
        vm.dates.push({
          title: contract.title + ' (End)',
          date: contract.end_date,
          future: isDateNotInPast(contract.end_date)
        });
      }
    }

    /**
     * Initialises subscribers
     */
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
        pubSub.subscribe(event, loadData);
      });

      pubSub.subscribe('Contract::deleted', function (contract) {
        Contract.removeContract(contract);
        loadData();
      });
    }

    /**
     * Checks if a date is in the future
     *
     * @param  {String} date
     * @return {Boolean}
     */
    function isDateNotInPast (date) {
      return moment().diff(date, 'days') <= 0;
    }

    /**
     * Loads contracts from the backend and sets their amount to the controller
     *
     * @return {Promise}
     */
    function loadContactsAmount () {
      return Contract.get()
        .then(function (response) {
          vm.activeContracts = _.values(response).filter(function (contract) {
            addContractDates(contract);

            return contract.is_current;
          }).length;
        });
    }

    /**
     * Fetches contacts and job roles from the backend
     *
     * @return {Promise}
     */
    function loadData () {
      vm.ready = false;

      resetKeyDates();

      return $q
        .all([
          loadContactsAmount(),
          loadJobRolesAmount()
        ])
        .finally(function () {
          vm.ready = true;
        });
    }

    /**
     * Loads job roles from the backend and sets their amount to the controller
     *
     * @return {Promise}
     */
    function loadJobRolesAmount () {
      return JobRole.get()
        .then(function (response) {
          vm.activeRoles = response.filter(function (role) {
            var endDate = moment(role.end_date);
            var hasEndDateAndIsActive = endDate.isValid() && isDateNotInPast(endDate);
            var doesNotHaveEndDate = endDate === undefined;

            return doesNotHaveEndDate || hasEndDateAndIsActive;
          }).length;
        })
        .catch(_.noop);
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
  }

  return { KeyDatesController: KeyDatesController };
});
