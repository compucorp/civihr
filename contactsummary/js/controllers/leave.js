define([
    'd3',
    'modules/controllers',
    'services/leave'
], function (d3, controllers) {
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
        this.totalTaken = 0;
        this.ready = false;
        this.chartColors = d3.scale.category20();

        Leave.getCurrent()
            .then(function (response) {
                angular.forEach(response, function (leave) {
                    if (leave.title !== 'Sick') {
                        self.totalEntitlement += leave.entitled;
                        self.totalTaken += leave.taken;

                        if (leave.title === 'TOIL') {
                            self.toil = leave;
                        } else {
                            self.leaves.push(leave);
                        }
                    }
                });
            })
            .finally(function () {
                self.ready = true;
            });
    }

    controllers.controller('LeaveCtrl', ['$log', 'LeaveService', LeaveCtrl]);
});
