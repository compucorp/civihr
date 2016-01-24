define([
    'common/angular',
    'common/moment',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (angular, moment, controllers) {
    'use strict';

    controllers.controller('AppraisalsDashboardCtrl',
        ['$filter', '$log', '$modal', '$rootElement', '$rootScope', '$scope', '$timeout',
        'AppraisalCycle', 'activeCycles', 'totalCycles', 'statusOverview',
        'statuses', 'types',
        function ($filter, $log, $modal, $rootElement, $rootScope, $scope, $timeout, AppraisalCycle, activeCycles, totalCycles, statusOverview, statuses, types) {
            $log.debug('AppraisalsDashboardCtrl');

            var pagination = { page: 1, size: 5 };
            var vm = {};

            vm.activeFilters = [
                { label: 'active', value: true },
                { label: 'inactive', value: false },
                { label: 'all', value: null }
            ];

            vm.cycles = { list: [], total: 0 };
            vm.filtersCollapsed = true;
            vm.filters = { cycle_is_active: vm.activeFilters[0].value };
            vm.loadingDone = false;

            vm.activeCycles = activeCycles;
            vm.totalCycles = totalCycles;
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

                if (typeof newValue !== 'undefined' && vm.filters.cycle_is_active !== newValue.value) {
                    vm.filters.cycle_is_active = newValue.value;
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
             * After retrieving the list of cycles, it also requests the status
             * overview for those cycles in the current week
             *
             * @param {boolean} addPage - If it's to request the next page
             */
            vm.requestCycles = function (addPage) {
                var cycles;

                if (addPage && vm.loadingDone) {
                    return;
                }

                pagination.page = !!addPage ? pagination.page + 1 : 1;

                AppraisalCycle.all(filters(), pagination)
                    .then(function (result) {
                        cycles = result;

                        return getStatusOverviewFor(cycles.allIds);
                    })
                    .then(function (statusOverview) {
                        processLoadedCycles(cycles, addPage);
                    });
            };

            init();

            /**
             * Attaches the listeners to the $rootScope
             */
            function addListeners() {
                $rootScope.$on('AppraisalCycle::new', function (event, newCycle) {
                    vm.cycles.list.unshift(newCycle);
                });

                $rootScope.$on('AppraisalCycle::edit', function (event, editedCycle) {
                    var i, len;

                    for (i = 0, len = vm.cycles.list.length; i < len; i++) {
                        if (vm.cycles.list[i].id === editedCycle.id) {
                            break;
                        }
                    }

                    vm.cycles.list.splice(i, 1, editedCycle);
                });
            }

            /**
             * Processes the selected appraisal cycle filters
             *
             * @return {object} a filters structure the model can use
             */
            function filters() {
                var filters = angular.copy(vm.filters);

                if (filters.cycle_is_active === null) {
                    delete filters.cycle_is_active;
                }

                Object.keys(filters).filter(function (key) {
                    return _.endsWith(key, '_date') || _.endsWith(key, '_from') || _.endsWith(key, '_to');
                }).forEach(function (key) {
                    filters[key] = $filter('date')(filters[key], 'dd/MM/yyyy');
                });

                return filters;
            }

            /**
             * Fetches the status overview limited to the given cycle ids
             * and the current week
             *
             * @param {string} cycleIds - A com
             * @return {Promise}
             */
            function getStatusOverviewFor(cycleIds) {
                var today = moment();

                return AppraisalCycle.statusOverview({
                    cycles_ids: cycleIds,
                    start_date: today.startOf('isoWeek').format('YYYY-MM-DD'),
                    end_date: today.endOf('isoWeek').format('YYYY-MM-DD')
                });
            }

            /**
             * Initialization code
             */
            function init() {
                addListeners();
                watchFilters();

                vm.requestCycles();
            }

            /**
             * Stores in the scope the returned cycles and checks if all the
             * cycles in the DB (that match the filters) have been loaded
             *
             * @param {object}
             *   The returned cycles' object comprised of the paginated list and
             *   the total number of cycles that match the filters
             * @param {boolean} add - If the list is to be added to the current
             *   list of cycles or it should overwrite it
             */
            function processLoadedCycles(cycles, add) {
                if (add) {
                    cycles.list.forEach(function (cycle) {
                        vm.cycles.list.push(cycle);
                    });
                } else {
                    vm.cycles.list = cycles.list;
                }

                vm.cycles.total = cycles.total;
                vm.loadingDone = vm.cycles.list.length === cycles.total;
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
