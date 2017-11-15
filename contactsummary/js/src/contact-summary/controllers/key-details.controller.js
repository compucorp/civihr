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

    (function init () {
      getContacts();
      pubSub.subscribe('contract-refresh', resetKeyDetails);
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
