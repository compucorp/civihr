define([
  'moment',
  'modules/controllers',
  'services/contactDetails',
  'services/contract'
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
  function KeyDetailsCtrl($log, ContactDetails, Contract) {
    $log.debug('Controller: KeyDetailsCtrl');

    var self = this;
    this.ready = false;

    ContactDetails.get()
      .then(function (response) {
        self.contactDetails = response;

        return Contract.getPrimary();
      })
      .then(function (response) {
        self.primaryContract = response;
        self.primaryContract.lengthOfService = getLengthOfService(response.start_date, response.end_date);
      })
      .finally(function () {
        self.ready = true;
      });

    /////////////////////
    // Private Members //
    /////////////////////

    function getLengthOfService(start, end) {
      start = moment(start, 'YYYY-MM-DD');
      end = end ? moment(end, 'YYYY-MM-DD') : moment();

      return moment.duration(end.diff(start)).humanize();
    }
  }

  controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', KeyDetailsCtrl]);
});
