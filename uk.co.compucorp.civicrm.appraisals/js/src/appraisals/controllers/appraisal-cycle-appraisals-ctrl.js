define([
    'common/lodash',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (_, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleAppraisalsCtrl', [
        '$log', '$scope', 'departments', 'levels', 'locations', 'regions',
        function ($log, $scope, departments, levels, locations, regions) {
            $log.debug('AppraisalCycleAppraisalsCtrl');

            var vm = {};

            vm.filtersCollapsed = true;
            vm.departments = departments;
            vm.levels = levels;
            vm.locations = locations;
            vm.regions = regions;
            vm.loading = { appraisals: true };

            $scope.$watch('cycle.loading.cycle', function (newValue) {
                !newValue && init();
            });

            /**
             * Initializing code
             *
             * Loads the cycle appraisals
             */
            function init() {
                $scope.cycle.cycle.loadAppraisals().then(function () {
                    vm.loading.appraisals = false;
                });
            }

            return vm;
        }
    ]);
});
