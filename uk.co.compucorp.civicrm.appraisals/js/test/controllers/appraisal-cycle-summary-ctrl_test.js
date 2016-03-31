define([
    'common/angularMocks',
    'appraisals/app',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-mock',
    'common/mocks/services/api/appraisal-cycle-mock',
], function () {
    'use strict';

    describe('AppraisalCycleSummaryCtrl', function () {
        var $controller, $log, $modal, $provide, $rootScope, $scope,
            appraisalAPI, appraisalCycleAPI, AppraisalCycle, ctrl, cycle;

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
            '$log', '$modal', '$controller', '$rootScope', 'AppraisalCycle',
            function (_$log_, _$modal_, _$controller_, _$rootScope_, _AppraisalCycle_) {
                ($modal = _$modal_) && spyOn($modal, 'open');
                ($log = _$log_) && spyOn($log, 'debug');

                $controller = _$controller_;
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();

                AppraisalCycle = _AppraisalCycle_;

                cycle = appraisalCycleAPI.mockedCycles().list[0];

                initController();
            }
        ]));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('contains an empty list of overdue appraisals', function () {
                expect(ctrl.overdueAppraisals).toEqual([]);
            });

            it('contains grades', function () {
                expect(ctrl.grades).toBeDefined();
                expect(ctrl.grades).toEqual(jasmine.any(Array));
            });

            it('is loading the overdue appraisals', function () {
                expect(ctrl.loading.overdue).toBe(true);
            });

            describe('before the overdue appraisals have been loaded', function () {
                beforeEach(function () {
                    $scope.$digest();
                });

                it('requests the overdue appraisals to store them in a dedicate ctrl property', function () {
                    expect($scope.cycle.cycle.loadAppraisals).toHaveBeenCalledWith({ overdue: true }, null, false);
                });
            });

            describe('when the overdue appraisals have been loaded', function () {
                beforeEach(function () {
                    $scope.$digest();
                });

                it('marks the appraisals as loaded', function () {
                    expect(ctrl.loading.overdue).toBe(false);
                });

                it('stores the overdue appraisals in a dedicated property', function () {
                    expect(ctrl.overdueAppraisals).not.toEqual([]);
                });
            });
        });

        describe('Edit Dates modal', function () {
            beforeEach(function () {
                ctrl.openEditDatesModal();
            });

            it('opens the modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalCycleSummaryCtrl', {
                $scope: initParentController()
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
