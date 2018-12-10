/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'common/models/job-role'
], function (angular, _, moment) {
  'use strict';

  KeyDatesController.$inject = ['$log', '$q', 'contractService', 'JobRole', 'pubSub', 'settings'];

  function KeyDatesController ($log, $q, Contract, JobRole, pubSub, settings) {
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
     * Loads contracts from the backend
     * and sets the amount of active contracts to the controller
     *
     * @return {Promise}
     */
    function loadContractsAmount () {
      return Contract.get()
        .then(function (response) {
          var contracts = _.values(response);

          contracts.forEach(addContractDates);

          vm.activeContracts = _.filter(contracts, { 'is_current': '1' }).length;
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
          loadContractsAmount(),
          loadJobRolesAmount()
        ])
        .finally(function () {
          vm.ready = true;
        });
    }

    /**
     * Fetches active job roles from the backend and sets their amount to the controller
     *
     * @return {Promise}
     */
    function loadJobRolesAmount () {
      return JobRole.activeForContact(settings.contactId)
        .then(function (activeRoles) {
          vm.activeRoles = activeRoles.length;
        });
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
