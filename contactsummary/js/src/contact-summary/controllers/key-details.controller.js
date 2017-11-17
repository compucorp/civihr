/* eslint-env amd */

define([
  'common/lodash',
  'common/moment'
], function (_, moment) {
  'use strict';

  KeyDetailsController.__name = 'KeyDetailsController';
  KeyDetailsController.$inject = ['$log', 'contactDetailsService', 'contractService', 'pubSub'];

  function KeyDetailsController ($log, ContactDetails, Contract, pubSub) {
    $log.debug('Controller: KeyDetailsController');

    var vm = this;

    vm.ready = false;
    vm.options = {};

    (function init () {
      initContractOptions();
      getContacts();
      initListeners();
    }());

    /**
     * Fetch Contacts from Server
     */
    function getContacts () {
      ContactDetails.get()
        .then(function (response) {
          vm.contactDetails = response;
          return Contract.getPrimary();
        })
        .then(function (response) {
          if (_.isEmpty(response)) {
            vm.primaryContract = null;
            return;
          }
          vm.primaryContract = response;
        })
        .then(function (response) {
          return Contract.getLengthOfService();
        })
        .then(function (response) {
          vm.lengthOfService = response;
        })
        .finally(function () {
          vm.ready = true;
        });
    }

    /**
     * Initialiazes the contract options
     */
    function initContractOptions () {
      vm.options = Contract.getOptions();
    }

    /**
     * Initialize Listeners
     */
    function initListeners () {
      pubSub.subscribe('contract-refresh', resetKeyDetails);
    }

    /**
     * Resets the details
     */
    function resetKeyDetails () {
      Contract.resetContracts();
      ContactDetails.data.item = {};
      getContacts();
    }
  }

  return KeyDetailsController;
});
