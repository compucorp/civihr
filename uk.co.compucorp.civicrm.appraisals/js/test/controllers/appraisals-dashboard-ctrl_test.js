define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsDashboardCtrl', function () {
        var $controller, $log, $scope, ctrl, AppraisalCycle;

        beforeEach(module('appraisals'));

        beforeEach(inject(function ($rootScope, _$log_, _$controller_, _AppraisalCycle_) {
            ($log = _$log_) && spyOn($log, 'debug');

            $controller = _$controller_;
            $scope = $rootScope.$new();
            AppraisalCycle = _AppraisalCycle_;

            spyOn(AppraisalCycle, 'all').and.callFake(function () {
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

                it('is requested on init, only the active cycles', function () {
                    expect(AppraisalCycle.all).toHaveBeenCalledWith({ active: true });
                });
            });
        });

        describe('after init', function () {
            beforeEach(function () {
                initController();
            });

            describe('active filter', function () {
                beforeEach(function () {
                    AppraisalCycle.all.calls.reset();
                });

                describe('when changing to a valid value', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('inactive');
                    });

                    it('sets the filter to the new value', function () {
                        expect(ctrl.filters.active).toBe(false);
                    });

                    it('makes a new request to the api', function () {
                        expect(AppraisalCycle.all).toHaveBeenCalledWith({ active: false });
                    });

                    describe('when changing to "all"', function () {
                        beforeEach(function () {
                            AppraisalCycle.all.calls.reset();
                            ctrl.changeActiveFilter('all');
                        });

                        it('removes the `active` property from `filters`', function () {
                            expect(AppraisalCycle.all).toHaveBeenCalledWith({});
                        });
                    });
                });

                describe('when changing to a invalid value', function () {
                    beforeEach(function () {
                        ctrl.changeActiveFilter('foo');
                    });

                    it('keep the old value set', function () {
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
        })

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
