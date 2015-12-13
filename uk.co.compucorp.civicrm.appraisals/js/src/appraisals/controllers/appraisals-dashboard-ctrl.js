define([
    'common/angular',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (angular, controllers) {
    'use strict';

    controllers.controller('AppraisalsDashboardCtrl',
        ['$log', '$modal', '$rootElement', '$rootScope', '$scope', '$timeout', 'AppraisalCycle', 'activeCycles', 'statusOverview', 'statuses', 'types',
        function ($log, $modal, $rootElement, $rootScope, $scope, $timeout, AppraisalCycle, activeCycles, statusOverview, statuses, types) {
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
             * Opens the modal to edit the cycle with the given id
             *
             * @param {string} id
             */
            vm.editCycle = function (id) {
                var modalScope = $rootScope.$new();
                modalScope.cycleId = id;

                $modal.open({
                    targetDomEl: $rootElement,
                    controller: 'AppraisalCycleModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    scope: modalScope,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/appraisal-cycle.html',
                });
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

                AppraisalCycle.all(filters(), pagination).then(function (cycles) {
                    if (addPage) {
                        cycles.list.forEach(function (cycle) {
                            vm.cycles.push(cycle);
                        });
                    } else {
                        vm.cycles = cycles.list;
                    }

                    vm.loadingDone = vm.cycles.length === cycles.total;
                });
            };

            init();


            /**
             * Attachs the listeners to the $rootScope
             */
            function addListeners() {
                $rootScope.$on('AppraisalCycle::new', function (event, newCycle) {
                    vm.cycles.unshift(newCycle);
                });

                $rootScope.$on('AppraisalCycle::edit', function (event, editedCycle) {
                    var i, len;

                    for (i = 0, len = vm.cycles.length; i < len; i++) {
                        if (vm.cycles[i].id === editedCycle.id) {
                            break;
                        }
                    }

                    vm.cycles.splice(i, 1, editedCycle);
                });
            }

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
                addListeners();
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
