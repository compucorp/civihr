define([
  'controllers/controllers',
  'moment',
  'services/contract'
], function (controllers, moment) {
  'use strict';

  /**
   * @ngdoc controller
   * @name KeyDatesCtrl
   * @param $log
   * @param {ContractService} Contract
   * @constructor
   */
  function KeyDatesCtrl($log, Contract) {
    $log.debug('Controller: KeyDatesCtrl');

    var self = this;
    this.ready = false;
    this.dates = [];

    Contract.get()
      .then(function (response) {
        angular.forEach(response, function (contract) {
          self.dates.push({title: contract.title + ' (Start)', date: contract.start_date});

          if (contract.end_date) {
            self.dates.push({title: contract.title + ' (End)', date: contract.end_date});
          }
        });
      })
      .finally(function () {
        self.ready = true;
      });
  }

  controllers.controller('KeyDatesCtrl', ['$log', 'ContractService', KeyDatesCtrl]);
});