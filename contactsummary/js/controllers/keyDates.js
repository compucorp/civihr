define([
  'controllers/controllers',
  'moment',
  'services/contract',
  'services/jobRole'
], function (controllers, moment) {
  'use strict';

  /**
   * @ngdoc controller
   * @name KeyDatesCtrl
   * @param $log
   * @param {ContractService} Contract
   * @param {JobRoleService} JobRole
   * @constructor
   */
  function KeyDatesCtrl($log, Contract, JobRole) {
    $log.debug('Controller: KeyDatesCtrl');

    var self = this;
    this.ready = false;
    this.dates = [];
    this.activeContracts = 0;
    this.activeRoles = 0;

    Contract.get()
      .then(function (response) {
        angular.forEach(response, function (contract) {
          self.dates.push({title: contract.title + ' (Start)', date: contract.start_date});

          if (contract.end_date) {
            self.dates.push({title: contract.title + ' (End)', date: contract.end_date});
          }

          if (contract.is_current) self.activeContracts++;
        });

        return JobRole.get();
      })
      .then(function (response) {
        angular.forEach(response, function (role) {
          if (role.status === '10') self.activeRoles++;
        });
      })
      .finally(function () {
        self.ready = true;
      });
  }

  controllers.controller('KeyDatesCtrl', ['$log', 'ContractService', 'JobRoleService', KeyDatesCtrl]);
});