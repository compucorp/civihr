define([
    'common/angular',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (angular, controllers) {
    'use strict';

    controllers.controller('AppraisalsDashboardCtrl',
        ['$log', '$scope', '$timeout', 'AppraisalCycle', 'activeCycles', 'statusOverview', 'statuses', 'types',
        function ($log, $scope, $timeout, AppraisalCycle, activeCycles, statusOverview, statuses, types) {
            $log.debug('AppraisalsDashboardCtrl');

            var pagination = { page: 1, size: 5 };
            var vm = {};

            vm.activeFilters = [
                { label: 'active', value: true },
                { label: 'inactive', value: false },
                { label: 'all', value: null }
            ];

            vm.cycles = [];
            vm.chartData = [];
            vm.filtersCollapsed = true;
            vm.filters = { active: vm.activeFilters[0].value };
            vm.loadingDone = false;

            vm.activeCycles = activeCycles;
            vm.statusOverview = statusOverview;
            vm.statuses = statuses;
            vm.types = types;

            /**
             * Changes the status filter to a new (valid) value
             *
             * @param {string} newValue
             */
            vm.changeActiveFilter = function (newValue) {
                newValue = vm.activeFilters.filter(function (filter) {
                    return filter.label === newValue;
                })[0];

                if (typeof newValue !== 'undefined' && vm.filters.active !== newValue.value) {
                    vm.filters.active = newValue.value;
                    vm.requestCycles();
                }
            };

            /**
             * Requests the model to return the list of cycles
             *
             * It can either add a new page worth of cycles to the existing list
             * or it can reset the entire list (in case new filters have been chosen)
             *
             * @param {boolean} addPage - If it's to request the next page
             */
            vm.requestCycles = function (addPage) {
                if (addPage && vm.loadingDone) {
                    return;
                }

                pagination.page = !!addPage ? pagination.page + 1 : 1;

                AppraisalCycle.all(filters(), pagination).then(function (cycles, totalCount) {
                    if (addPage) {
                        vm.cycles.push(cycles);
                    } else {
                        vm.cycles = cycles;
                    }

                    vm.loadingDone = vm.cycles.length === totalCount;
                });
            };

            init();

            /**
             * Processes the selected appraisal cycle filters
             *
             * @return {object} a filters structure the model can use
             */
            function filters() {
                var filters = angular.copy(vm.filters);

                if (filters.active === null) {
                    delete filters.active;
                }

                return filters;
            }

            /**
             * Initialization code
             */
            function init() {
                watchFilters();
                vm.requestCycles();

                AppraisalCycle.grades().then(function (grades) {
                    vm.chartData = grades;
                });
            }


            /**
             * Checks when the filter values change, then wait for a delay
             * before requesting the new list filtered with the new criteria
             */
            function watchFilters() {
                var timeout = null;

                $scope.$watch(function () {
                    return vm.filters;
                }, function (newValue, oldValue) {
                    if (newValue !== oldValue ) {
                        $timeout.cancel(timeout);
                        timeout = $timeout(vm.requestCycles, 500)
                    }
                }, true);
            }

            return vm;
        }]
    );
});
