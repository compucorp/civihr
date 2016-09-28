define([
  'common/moment',
  'contact-summary/modules/controllers',
  'contact-summary/services/contactDetails',
  'contact-summary/services/contract',
  'common/services/pub-sub',
], function (moment, controllers) {
  'use strict';

  /**
   * @ngdoc controller
   * @name KeyDetailsCtrl
   * @param $log
   * @param {ContactDetailsService} ContactDetails
   * @param {ContractService} Contract
   * @param {pubSub} pubSub
   * @constructor
   */
  function KeyDetailsCtrl($log, ContactDetails, Contract, pubSub) {
    $log.debug('Controller: KeyDetailsCtrl');

    this.ready = false;


    /**
     * Fetch Contacts from Server
     * @ngdoc method
     * @name getContacts
     * @methodOf KeyDetailsCtrl
     * @returns void
     */
    var getContacts = function(){
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

    var resetKeyDetails = function() {
      Contract.resetContracts();
      ContactDetails.data.item = {};
      getContacts();
    };

    getContacts();

    pubSub.subscribe('contract-refresh',  resetKeyDetails);
  }

  /////////////////////
  // Private Members //
  /////////////////////

  controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', 'pubSub', KeyDetailsCtrl]);
});
