/* eslint-env amd */

define([
  'common/lodash',
  'common/moment'
], function (_, moment) {
  'use strict';

  KeyDetailsCtrl.__name = 'KeyDetailsCtrl';
  KeyDetailsCtrl.$inject = ['$log', 'ContactDetailsService', 'ContractService', 'pubSub'];

  function KeyDetailsCtrl ($log, ContactDetails, Contract, pubSub) {
    $log.debug('Controller: KeyDetailsCtrl');

    this.ready = false;

    /**
     * Fetch Contacts from Server
     * @ngdoc method
     * @name getContacts
     * @methodOf KeyDetailsCtrl
     * @returns void
     */
    var getContacts = function () {
      ContactDetails.get()
        .then(function (response) {
          this.contactDetails = response;
          return Contract.getPrimary();
        }.bind(this))
        .then(function (response) {
          if (_.isEmpty(response)) {
            this.primaryContract = null;
            return;
          }
          this.primaryContract = response;
        }.bind(this))
        .then(function (response) {
          return Contract.getLengthOfService();
        })
        .then(function (response) {
          this.lengthOfService = response;
        }
          .bind(this))
        .finally(function () {
          this.ready = true;
        }.bind(this));
    }.bind(this);

    var resetKeyDetails = function () {
      Contract.resetContracts();
      ContactDetails.data.item = {};
      getContacts();
    };

    getContacts();

    pubSub.subscribe('contract-refresh', resetKeyDetails);
  }

  return KeyDetailsCtrl;
});
