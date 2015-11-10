define([
  'modules/controllers',
  'services/leave'
], function (controllers) {
  'use strict';

  /**
   * @ngdoc controller
   * @name SicknessCtrl
   * @param $log
   * @param {LeaveService} Leave
   * @constructor
   */
  function SicknessCtrl($log, Leave) {
    $log.debug('Controller: SicknessCtrl ');

    var self = this;

    this.taken = 0;
    this.ready = false;

    Leave.get()
      .then(function (response) {
        angular.forEach(response, function (leave) {
          if (leave.title === 'Sick') {
            self.taken = leave.taken;
          }
        });
      })
      .finally(function () {
        self.ready = true;
      });
  }

  controllers.controller('SicknessCtrl', ['$log', 'LeaveService', SicknessCtrl]);
});
