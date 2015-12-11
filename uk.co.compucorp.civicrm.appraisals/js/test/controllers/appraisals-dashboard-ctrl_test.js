define([
    'common/angular',
    'common/angularMocks',
    'appraisals/app',
], function (angular) {
    'use strict';

    describe('AppraisalsDashboardCtrl', function () {
        var $controller, $log, $scope, $timeout, ctrl, AppraisalCycle;

        beforeEach(module('appraisals'));

        beforeEach(inject(function ($rootScope, _$log_, _$timeout_, _$controller_, _AppraisalCycle_) {
            ($log = _$log_) && spyOn($log, 'debug');

            $controller = _$controller_;
            $timeout = _$timeout_;
            $scope = $rootScope.$new();

            (AppraisalCycle = _AppraisalCycle_) && spyOn(AppraisalCycle, 'all').and.callFake(function () {
                return { then: function () {} };
            });
        }));

        describe('init', function () {
            describe('general initial state', function () {
                beforeEach(function () {
                    initController();
                });

                it('is initialized', function () {
                    expect($log.debug).toHaveBeenCalled();
                });

                it('stores on scope the data passed by ui-router', function () {
                    expect(ctrl.activeCycles).toBeDefined();
                    expect(ctrl.statusOverview).toBeDefined();
                    expect(ctrl.statuses).toBeDefined();
                    expect(ctrl.types).toBeDefined();
                });

                it('it does not consider the full list of cycles loaded', function () {
                    expect(ctrl.loadingDone).toBe(false);
                });

                it('has the filters form collapsed', function () {
                    expect(ctrl.filtersCollapsed).toBe(true);
                });

                it('has the cycle active filter set to "active"', function () {
                    expect(ctrl.filters.active).toBe(true);
                });
            });

            describe('grades chart data', function () {
                beforeEach(function () {
                    spyOn(AppraisalCycle, 'grades').and.callFake(function () {
                        return { then: function () {} };
                    });

                    initController();
                });

                it('starts as an empty array', function () {
                    expect(ctrl.chartData).toEqual([]);
                });

                it('is requested on init', function () {
                    expect(AppraisalCycle.grades).toHaveBeenCalled();
                });
            });

            describe('cycles list', function () {
                beforeEach(function () {
                    initController();
                });

                it('has appraisal cycles list as an empty array', function () {
                    expect(ctrl.cycles).toEqual([]);
                });

                it('requires the first page of active cycles', function () {
                    expect(AppraisalCycle.all).toHaveBeenCalledWith({ active: true }, { page: 1, size: 5 });
                });
            });
        });

        describe('after init', function () {
            beforeEach(function () {
                initController();
                AppraisalCycle.all.calls.reset();
            });

            describe('active filter', function () {
                describe('when changing to a valid value', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('inactive');
                    });

                    it('sets the filter to the new value', function () {
                        expect(ctrl.filters.active).toBe(false);
                    });

                    it('makes a new request to the api', function () {
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ active: false }, { page: 1, size: 5 });
                    });

                    describe('when changing to "all"', function () {
                        beforeEach(function () {
                            AppraisalCycle.all.calls.reset();
                            ctrl.changeActiveFilter('all');
                        });

                        it('removes the `active` property from `filters`', function () {
                            expect(AppraisalCycle.all).toHaveBeenCalledWith({}, { page: 1, size: 5 });
                        });
                    });
                });

                describe('when changing to a invalid value', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('foo');
                    });

                    it('keeps the old value set', function () {
                        expect(ctrl.filters.active).toBe(true);
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
                    active: true,
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
                    expect(AppraisalCycle.all).toHaveBeenCalledWith(selectedFilters, { page: 1, size: 5 });
                });
            });

            describe('pagination', function () {

                describe('when the full list has not been loaded yet', function () {
                    beforeEach(function () {
                        ctrl.requestCycles(true);
                    });

                    it('can request the next page', function () {
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ active: true }, { page: 2, size: 5 });
                    });
                });

                describe('when full list has been loaded', function () {
                    beforeEach(function () {
                        ctrl.loadingDone = true;
                    });

                    it('cannot request a next page', function () {
                        ctrl.requestCycles(true);
                        expect(AppraisalCycle.all).not.toHaveBeenCalled();
                    });

                    it('can request the first page again', function () {
                        ctrl.requestCycles();
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ active: true }, { page: 1, size: 5 });
                    });
                });
            });
        });

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalsDashboardCtrl', {
                $scope: $scope,
                activeCycles: [],
                statusOverview: [],
                statuses: [],
                types: []
            });
        }
    });
})
