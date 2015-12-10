define([
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (controllers) {
    'use strict';

    controllers.controller('AppraisalsDashboardCtrl',
        ['$log', 'AppraisalCycle', 'statuses', 'types',
        function ($log, AppraisalCycle, statuses, types) {
            $log.debug('AppraisalsDashboardCtrl');

            var vm = {};
            vm.chartData = [];
            vm.filtersCollapsed = true;
            vm.statuses = statuses;
            vm.types = types;

            init();

            /**
             * Initialization code
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
