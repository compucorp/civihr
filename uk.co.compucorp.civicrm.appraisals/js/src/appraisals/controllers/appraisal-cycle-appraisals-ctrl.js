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

            vm.filters = {};
            vm.filtersCollapsed = true;
            vm.loading = { appraisals: true };
            vm.pagination = { page: 1, size: 5 };

            vm.departments = departments;
            vm.levels = levels;
            vm.locations = locations;
            vm.regions = regions;

            /**
             * Changes the current page of the appraisals list
             *
             * @param {int} pageNo
             */
            vm.setPage = function setPage(pageNo) {
                vm.loading.appraisals = true;
                vm.pagination.page = pageNo;

                $scope.cycle.cycle.loadAppraisals(vm.filters, vm.pagination)
                    .then(function () {
                        vm.loading.appraisals = false;
                    });
            };

            $scope.$watch('cycle.loading.cycle', function (newValue) {
                !newValue && init();
            });

            /**
             * Initializing code
             *
             * Loads the cycle appraisals
             */
            function init() {
                $scope.cycle.cycle.loadAppraisals(vm.filters, vm.pagination)
                    .then(function () {
                        vm.loading.appraisals = false;
                    });
            }

            return vm;
        }
    ]);
});
