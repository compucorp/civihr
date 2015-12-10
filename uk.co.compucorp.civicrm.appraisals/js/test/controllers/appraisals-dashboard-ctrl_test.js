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
        }));

        describe('init', function () {
            beforeEach(function () {
                spyOn(AppraisalCycle, 'grades').and.callFake(function () {
                    return { then: function () {} };
                });

                initController();
            });

            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('is stores on scope the data passed by ui-router', function () {
                expect(ctrl.statusOverview).toBeDefined();
                expect(ctrl.statuses).toBeDefined();
                expect(ctrl.types).toBeDefined();
            });

            it('has the filters form collapsed', function () {
                expect(ctrl.filtersCollapsed).toBe(true);
            });

            it('has chartData as an empty array', function () {
                expect(ctrl.chartData).toEqual([]);
            });

            it('requests the grades data', function () {
                expect(AppraisalCycle.grades).toHaveBeenCalled();
            });
        });

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalsDashboardCtrl', {
                $scope: $scope,
                statusOverview: [],
                statuses: [],
                types: []
            });
        }
    });
})
