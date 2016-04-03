define([
    'common/angularMocks',
    'appraisals/app',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-mock',
    'common/mocks/services/api/appraisal-cycle-mock',
], function () {
    'use strict';

    describe('AppraisalCycleAppraisalsCtrl', function () {
        var $controller, $log, $modal, $provide, $rootScope, $scope,
            AppraisalCycle, appraisalAPI, appraisalCycleAPI, ctrl, dialog, cycle;

        beforeEach(function () {
            module('appraisals', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });

            inject(['api.appraisal.mock', 'api.appraisal-cycle.mock', 'HR_settingsMock',
                function (_appraisalAPIMock_, _appraisalCycleAPIMock_, HR_settingsMock) {
                    appraisalAPI = _appraisalAPIMock_;
                    appraisalCycleAPI = _appraisalCycleAPIMock_;

                    $provide.value('api.appraisal', appraisalAPI);
                    $provide.value('api.appraisal-cycle', appraisalCycleAPI);
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        });
        beforeEach(inject([
            '$log', '$modal', '$controller', '$rootScope', 'dialog', 'AppraisalCycle',
            function (_$log_, _$modal_, _$controller_, _$rootScope_, _dialog_, _AppraisalCycle_) {
                ($modal = _$modal_) && spyOn($modal, 'open');
                ($log = _$log_) && spyOn($log, 'debug');

                $controller = _$controller_;
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();

                AppraisalCycle = _AppraisalCycle_;
                dialog = _dialog_;

                cycle = appraisalCycleAPI.mockedCycles().list[0];

                initController();
            }
        ]));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('stores on scope the data passed by ui-router', function () {
                expect(ctrl.departments).toBeDefined();
                expect(ctrl.levels).toBeDefined();
                expect(ctrl.locations).toBeDefined();
                expect(ctrl.regions).toBeDefined();
            });

            it('is loading the cycle appraisals', function () {
                expect(ctrl.loading.appraisals).toBe(true);
            });

            it('does not have any filter set', function () {
                expect(ctrl.filters).toEqual({});
            });

            it('has the current page set to the first page', function () {
                expect(ctrl.pagination.page).toBe(1);
            });

            it('has the page size set to 5', function () {
                expect(ctrl.pagination.size).toBe(5);
            });

            it('has the filters form collapsed', function () {
                expect(ctrl.filtersCollapsed).toBe(true);
            });

            describe('before the appraisals have been loaded', function () {
                beforeEach(function () {
                    $scope.$digest();
                });

                it('triggers the call to the method that loads them', function () {
                    expect($scope.cycle.cycle.loadAppraisals).toHaveBeenCalledWith(
                        ctrl.filters,
                        { page: 1, size: 5 }
                    );
                });
            });

            describe('when the appraisals have been loaded', function () {
                beforeEach(function () {
                    $scope.$digest();
                });

                it('marks the appraisals as loaded', function () {
                    expect(ctrl.loading.appraisals).toBe(false);
                });
            });
        });

        describe('afterInit()', function () {
            var newPage = 4;

            beforeEach(function () {
                $scope.$digest();
            });

            describe('setPage()', function () {
                beforeEach(function () {
                    ctrl.setPage(newPage);
                });

                it('marks the appraisals as loading', function () {
                    expect(ctrl.loading.appraisals).toBe(true);
                });

                it('changes the current page of the appraisals list', function () {
                    expect(ctrl.pagination.page).toBe(newPage);
                });

                it('fetches the appraisals for that page', function () {
                    expect($scope.cycle.cycle.loadAppraisals).toHaveBeenCalledWith(
                        ctrl.filters,
                        { page: newPage, size: 5 }
                    );
                });

                describe('after appraisals have been loaded', function () {
                    beforeEach(function () {
                        $scope.$digest();
                    });

                    it('marks the appraisals as loaded', function () {
                        expect(ctrl.loading.appraisals).toBe(false);
                    });
                });
            });
        });

        /**
         * Initializes the controller with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalCycleAppraisalsCtrl', {
                $scope: initParentController(),
                departments: [],
                levels: [],
                locations: [],
                regions: []
            });
        }

        /**
         * Initializes the parent controller with its dependencies injected
         *
         * @return the $scope object with the parent controller property in it
         */
        function initParentController() {
            $scope.cycle = $controller('AppraisalCycleCtrl', {
                $scope: $rootScope.$new(),
                $stateParams: { cycleId: cycle.id },
                AppraisalCycle: AppraisalCycle,
                statuses: [],
                types: []
            });

            $scope.$digest();
            spyOn($scope.cycle.cycle, 'loadAppraisals').and.callThrough();

            return $scope;
        }
    });
});
