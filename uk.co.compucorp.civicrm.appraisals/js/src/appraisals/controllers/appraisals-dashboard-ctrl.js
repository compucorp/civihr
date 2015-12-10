define([
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (controllers) {
    'use strict';

    controllers.controller('AppraisalsDashboardCtrl',
        ['$log', 'AppraisalCycle',
        function ($log, AppraisalCycle) {
            $log.debug('AppraisalsDashboardCtrl');

            var vm = {};
            vm.chartData = [];
            vm.filtersCollapsed = true;

            init();

            /**
             *
             *
             */
            function init() {
                AppraisalCycle.grades().then(function (grades) {
                    vm.chartData = grades;
                });
            }

            return vm;
        }]
    );
});
