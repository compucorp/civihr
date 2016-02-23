define([
    'common/lodash',
    'common/angularMocks',
    'common/mocks/services/api/appraisal-cycle-mock',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('EditDatesModalCtrl', function () {
        var $filter, $modalInstance, $provide, $rootScope, appraisalCycleAPIMock,
            cycle, ctrl;

        beforeEach(function () {
            module('appraisals', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject(['api.appraisal-cycle.mock',
                function (_appraisalCycleAPIMock_) {
                    appraisalCycleAPIMock = _appraisalCycleAPIMock_;

                    $provide.value('api.appraisal-cycle', appraisalCycleAPIMock);
                }
            ]);
        });
        beforeEach(inject([
            '$controller', '$rootScope', 'AppraisalCycleInstance',
            function ($controller, _$rootScope_, AppraisalCycleInstance) {
                $rootScope = _$rootScope_;
                cycle = AppraisalCycleInstance.init(
                    appraisalCycleAPIMock.mockedCycles().list[2]
                );

                createSpies();

                ctrl = $controller('EditDatesModalCtrl', {
                    $filter: $filter,
                    $modalInstance: $modalInstance,
                    $scope: (function (scope) {
                        scope.cycle = cycle;
                        return scope;
                    })($rootScope.$new())
                });
            }
        ]));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('cycle instance', function () {
            it('contains the cycle in its scope', function () {
                expect(ctrl.cycle).toBeDefined();
                expect(ctrl.cycle.id).toBe(cycle.id);
            });

            it('makes a copy of the cycle, not working directly on it', function () {
                expect(ctrl.cycle).not.toBe(cycle);
            });
        });

        describe('submit()', function () {
            beforeEach(function () {
                spyOn(ctrl.cycle, 'update').and.callThrough();
                spyOn($rootScope, '$emit');

                ctrl.submit();
                $rootScope.$digest();
            });

            it('formats the datepicker dates', function () {
                expect($filter).toHaveBeenCalledWith('date');
            });

            it('updates the cycle', function () {
                expect(ctrl.cycle.update).toHaveBeenCalled();
            });

            it('emits an event', function () {
                expect($rootScope.$emit).toHaveBeenCalledWith('AppraisalCycle::edit', ctrl.cycle);
            });

            it('closes the modal', function () {
                expect($modalInstance.close).toHaveBeenCalled();
            });
        });

        /**
         * Creates fake functions to inject in the controller
         */
        function createSpies() {
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);
            $filter = jasmine.createSpy('filter').and.callFake(function () {
                return _.noop;
            });
        }
    });
});
