define([
  'common/moment',
  'contact-summary/modules/controllers',
  'contact-summary/services/contactDetails',
  'contact-summary/services/contract',
  'common/services/pub-sub-service',
], function (moment, controllers) {
  'use strict';

  /**
   * @ngdoc controller
   * @name KeyDetailsCtrl
   * @param $log
   * @param {ContactDetailsService} ContactDetails
   * @param {ContractService} Contract
   * @constructor
   */
  function KeyDetailsCtrl($log, ContactDetails, Contract, pubSubService) {
    $log.debug('Controller: KeyDetailsCtrl');

    this.ready = false;



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

    pubSubService.subscribe("contract-refresh",  resetKeyDetails);
  }

  /////////////////////
  // Private Members //
  /////////////////////

  controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', 'pubSubService', KeyDetailsCtrl]);
});
