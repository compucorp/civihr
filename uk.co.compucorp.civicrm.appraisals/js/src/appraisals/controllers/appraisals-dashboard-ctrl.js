define([
    'common/angular',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (angular, controllers) {
    'use strict';

    controllers.controller('AppraisalsDashboardCtrl',
        ['$log', 'AppraisalCycle', 'activeCycles', 'statusOverview', 'statuses', 'types',
        function ($log, AppraisalCycle, activeCycles, statusOverview, statuses, types) {
            $log.debug('AppraisalsDashboardCtrl');

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
                    requestCycles();
                }
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
                AppraisalCycle.grades().then(function (grades) {
                    vm.chartData = grades;
                });

                requestCycles();
            }

            /**
             * Requests the model to return the list of cycles
             */
            function requestCycles() {
                AppraisalCycle.all(filters()).then(function (cycles) {
                    vm.cycles = cycles;
                });
            }

            return vm;
        }]
    );
});
