define([
    'common/angular',
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-cycle-mock',
    'appraisals/app',
], function (angular) {
    'use strict';

    describe('AppraisalsDashboardCtrl', function () {
        var $controller, $log, $modal, $provide, $rootScope, $scope, $timeout, ctrl, AppraisalCycle;

        beforeEach(function () {
            module('appraisals', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject([
                'api.appraisal-cycle.mock', 'HR_settingsMock',
                function (_appraisalCycleAPIMock_, HR_settingsMock) {
                    $provide.value('api.appraisal-cycle', _appraisalCycleAPIMock_);
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        });

        beforeEach(inject(function (_$rootScope_, _$log_, _$uibModal_, _$timeout_, _$controller_, _AppraisalCycle_) {
            ($log = _$log_) && spyOn($log, 'debug');

            $controller = _$controller_;
            $modal = _$uibModal_;
            $rootScope = _$rootScope_;
            $scope = $rootScope.$new();
            $timeout = _$timeout_;

            AppraisalCycle = _AppraisalCycle_;
        }));

        describe('init', function () {
            beforeEach(function () {
                spyOn(AppraisalCycle, 'all').and.callThrough();
                initController();
            });

            describe('general initial state', function () {
                it('is initialized', function () {
                    expect($log.debug).toHaveBeenCalled();
                });

                it('stores on scope the data passed by ui-router', function () {
                    expect(ctrl.activeCycles).toBeDefined();
                    expect(ctrl.totalCycles).toBeDefined();
                    expect(ctrl.statusOverview).toBeDefined();
                    expect(ctrl.statuses).toBeDefined();
                    expect(ctrl.types).toBeDefined();
                });

                it('does not consider the full list of cycles loaded', function () {
                    expect(ctrl.loading.done).toBe(false);
                });

                it('is loading cycles', function () {
                    expect(ctrl.loading.inProgress).toBe(true);
                });

                it('has the filters form collapsed', function () {
                    expect(ctrl.filtersCollapsed).toBe(true);
                });

                it('has the cycle active filter set to "active"', function () {
                    expect(ctrl.filters.cycle_is_active).toBe(true);
                });

                it('has the appraisals weekly figures set to 0', function () {
                    expect(ctrl.appraisalsWeeklyFigures).toBeDefined();
                    expect(ctrl.appraisalsWeeklyFigures.due).toBe(0);
                    expect(ctrl.appraisalsWeeklyFigures.overdue).toBe(0);
                });

                describe('cycles', function () {
                    it('it is as an object', function () {
                        expect(ctrl.cycles).toEqual(jasmine.any(Object));
                    });

                    it('it contains an array as the cycles list', function () {
                        expect(ctrl.cycles.list).toBeDefined();
                        expect(ctrl.cycles.list).toEqual([]);
                    });

                    it('it contains the total number of found cycles', function () {
                        expect(ctrl.cycles.total).toBeDefined();
                        expect(ctrl.cycles.total).toEqual(0);
                    });
                });
            });

            it('requires the first page of active cycles', function () {
                expect(AppraisalCycle.all).toHaveBeenCalledWith({ cycle_is_active: true }, { page: 1, size: 5 });
            });
        });

        describe('after init', function () {
            beforeEach(function () {
                initController();

                spyOn(AppraisalCycle, 'all').and.callThrough();
                spyOn(AppraisalCycle, 'statusOverview').and.callFake(function () {
                    return fakeStatusOverviewResponse()
                });
            });

            describe('when requesting cycles', function () {
                beforeEach(function () {
                    $scope.$digest();

                    AppraisalCycle.statusOverview.calls.reset();
                });

                describe('model call', function () {
                    beforeEach(function () {
                        ctrl.requestCycles();
                        $scope.$digest();
                    });

                    it('sets the loading in progress flag to false', function () {
                        expect(ctrl.loading.inProgress).toBe(false);
                    })

                    it('uses the AppraisalCycle model', function () {
                        expect(AppraisalCycle.all).toHaveBeenCalled();
                    });
                });

                describe('when requesting the first page', function ( ){
                    beforeEach(function () {
                        jasmine.clock().mockDate(new Date(2016, 0, 5));

                        ctrl.requestCycles();
                        $scope.$digest();
                    });

                    describe('status overview', function () {
                        it('requests also a status overview', function () {
                            expect(AppraisalCycle.statusOverview).toHaveBeenCalled();
                        });

                        it('request it limited to the current week', function () {
                            expect(AppraisalCycle.statusOverview).toHaveBeenCalledWith(jasmine.objectContaining({
                                start_date: '2016-01-04',
                                end_date: '2016-01-10',
                            }));
                        });

                        it('requests it limited to the returned cycles', function () {
                            expect(AppraisalCycle.statusOverview).toHaveBeenCalledWith(jasmine.objectContaining({
                                cycles_ids: ctrl.cycles.list.map(function (cycle) {
                                    return cycle.id;
                                }).join(',')
                            }));
                        });

                        it('updates the weekly figures', function () {
                            var weeklyFigures = _.reduce(fakeStatusOverviewResponse().steps, function (figures, status) {
                                figures.due += status.due;
                                figures.overdue += status.overdue;

                                return figures;
                            }, { due: 0, overdue: 0 });

                            expect(ctrl.appraisalsWeeklyFigures).toEqual(weeklyFigures)
                        });
                    });
                });

                describe('when requesting the next page', function () {
                    beforeEach(function () {
                        ctrl.requestCycles(true);
                        $scope.$digest();
                    });

                    describe('status overview', function () {
                        it('does not request a status overview', function () {
                            expect(AppraisalCycle.statusOverview).not.toHaveBeenCalled();
                        });
                    });
                });
            });

            describe('active filter', function () {
                describe('when changing to a valid value', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('inactive');
                    });

                    it('sets the filter to the new value', function () {
                        expect(ctrl.filters.cycle_is_active).toBe(false);
                    });

                    it('makes a new request to the api', function () {
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ cycle_is_active: false }, jasmine.any(Object));
                    });

                    describe('when changing to "all"', function () {
                        beforeEach(function () {
                            AppraisalCycle.all.calls.reset();
                            ctrl.changeActiveFilter('all');
                        });

                        it('removes the `active` property from `filters`', function () {
                            expect(AppraisalCycle.all).toHaveBeenCalledWith({}, jasmine.any(Object));
                        });
                    });
                });

                describe('when changing to a invalid value', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('foo');
                    });

                    it('keeps the old value set', function () {
                        expect(ctrl.filters.cycle_is_active).toBe(true);
                    });

                    it('does not make a new request to the api', function () {
                        expect(AppraisalCycle.all).not.toHaveBeenCalled();
                    });
                });

                describe('when changing to the same value already set', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('inactive');
                        AppraisalCycle.all.calls.reset();

                        ctrl.changeActiveFilter('inactive');
                    });

                    it('does not make a new request to the api', function () {
                        expect(AppraisalCycle.all).not.toHaveBeenCalled();
                    });
                })
            });

            describe('filters', function () {
                var selectedFilters = {
                    cycle_is_active: true,
                    name: 'foo',
                    status: 'bar',
                    type: 'baz'
                };

                beforeEach(function () {
                    $scope.$digest();
                    angular.extend(ctrl.filters, selectedFilters);
                    $scope.$digest();
                    $timeout.flush();
                });

                it('makes a new request to the api with the selected filters', function () {
                    expect(AppraisalCycle.all).toHaveBeenCalledWith(selectedFilters, jasmine.any(Object));
                });
            });

            describe('pagination', function () {
                describe('when the full list has not been loaded yet', function () {
                    beforeEach(function () {
                        ctrl.requestCycles(true);
                    });

                    it('can request the next page', function () {
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ cycle_is_active: true }, { page: 2, size: 5 });
                    });
                });

                describe('when full list has been loaded', function () {
                    beforeEach(function () {
                        ctrl.loading.done = true;
                    });

                    it('cannot request a next page', function () {
                        ctrl.requestCycles(true);
                        expect(AppraisalCycle.all).not.toHaveBeenCalled();
                    });

                    it('can request the first page again', function () {
                        ctrl.requestCycles();
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ cycle_is_active: true }, { page: 1, size: 5 });
                    });
                });
            });

            describe('when a new cycle is added', function () {
                var beforeTotal, newCycle;

                newCycle = { id: '4567', name: 'The new cycle' };

                beforeEach(function () {
                    $scope.$digest();
                    beforeTotal = ctrl.cycles.list.length;

                    $rootScope.$emit('AppraisalCycle::new', newCycle);
                    $scope.$digest();
                });

                it('adds it to the top of cycles list', function () {
                    expect(ctrl.cycles.list.length).toBe(beforeTotal + 1);
                    expect(ctrl.cycles.list[0].name).toEqual(newCycle.name);
                });
            });

            describe('edit cycle', function () {
                var cycleId = '7';

                beforeEach(function () {
                    spyOn($modal, 'open').and.callThrough();
                    ctrl.editCycle(cycleId);
                });

                it('opens a modal with the cycle id passed to the scope', function () {
                    expect($modal.open).toHaveBeenCalledWith(jasmine.objectContaining({
                        scope: jasmine.objectContaining({ cycleId: cycleId })
                    }));
                });
            });

            describe('when a cycle had been edited', function () {
                var beforeTotal, newData;

                newData = { name: 'foo', type: 'bar' };

                beforeEach(function () {
                    $scope.$digest();
                    beforeTotal = ctrl.cycles.list.length;
                    angular.extend(newData, { id: ctrl.cycles.list[3].id });

                    $rootScope.$emit('AppraisalCycle::edit', newData);
                    $scope.$digest();
                });

                it('updates the list', function () {
                    expect(ctrl.cycles.list.length).toBe(beforeTotal);
                    expect(ctrl.cycles.list.filter(function (cycle) {
                        return cycle.id == newData.id;
                    })[0]).toEqual(newData);
                });
            });
        });

        /**
         * Fake data to simulate a response from AppraisalCycle.statusOverview()
         *
         * @return {object}
         */
        function fakeStatusOverviewResponse() {
            return {
                steps: {
                    1: { name: 'Step #1', due: 2, overdue: 12 },
                    3: { name: 'Step #2', due: 12, overdue: 3 },
                    4: { name: 'Step #3', due: 7, overdue: 0 },
                    5: { name: 'Step #4', due: 0, overdue: 18 },
                    6: { name: 'Step #5', due: 9, overdue: 9 }
                },
                totalAppraisalNumber: 78
            };
        }

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalsDashboardCtrl', {
                $scope: $scope,
                AppraisalCycle: AppraisalCycle,
                activeCycles: [],
                totalCycles: [],
                statusOverview: [],
                statuses: [],
                types: []
            });
        }
    });
})
