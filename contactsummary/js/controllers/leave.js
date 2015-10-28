define([
  'controllers/controllers',
  'services/leave',
  'directives/donutChart'
], function (controllers) {
  'use strict';

  /**
   * @ngdoc controller
   * @name LeaveCtrl
   * @param $log
   * @param {LeaveService} Leave
   * @constructor
   */
  function LeaveCtrl($log, Leave) {
    $log.debug('Controller: LeaveCtrl');

    var self = this;

    this.leaves = [];
    this.toil = {};
    this.totalEntitlement = 0;
    this.ready = false;

    Leave.get()
      .then(function (response) {
        angular.forEach(response, function (leave) {
          self.totalEntitlement += leave.entitled;

          if (leave.title !== 'Sick') {
            if (leave.title === 'TOIL') self.toil = leave;
            else self.leaves.push(leave);
          }
        });
      })
      .finally(function () {
        self.ready = true;
      });
  }

  controllers.controller('LeaveCtrl', ['$log', 'LeaveService', LeaveCtrl]);
});