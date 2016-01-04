define([
    'common/angular',
    'common/angularMocks',
    'appraisals/app',
    'mocks/models/appraisal-cycle'
], function (angular) {
    'use strict';

    describe('AppraisalCycleModalCtrl', function () {
        var $controller, $q, $modalInstance, $rootScope, AppraisalCycle, ctrl;

        beforeEach(module('appraisals', 'appraisals.mocks'));
        beforeEach(inject(function (_$controller_, _$q_, _$rootScope_, _AppraisalCycleMock_) {
            $controller = _$controller_;
            $q = _$q_;
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);
            $rootScope = _$rootScope_;
            AppraisalCycle = _AppraisalCycleMock_;

            initController();
        }));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('init', function () {
            describe('cycle types list', function () {
                it('waits for data to be loaded', function () {
                    expect(ctrl.loaded.types).toBe(false);
                });

                it('requests the list to the model', function () {
                    expect(AppraisalCycle.types).toHaveBeenCalled();
                });

                describe('when the model returns the data', function () {
                    beforeEach(function () {
                        $rootScope.$digest();
                    });

                    it('marks the list as loaded', function () {
                        expect(ctrl.loaded.types).toBe(true);
                    });
                });
            });

            describe('when in "create mode"', function () {
                it('marks the flag as such', function () {
                    expect(ctrl.edit).toBe(false);
                });

                it('does not fetch the data of any cycle', function () {
                    expect(AppraisalCycle.find).not.toHaveBeenCalled();
                    expect(ctrl.cycle).toEqual({});
                    expect(ctrl.loaded.cycle).toBe(true);
                });
            });

            describe('when in "edit mode', function () {
                var $scope;

                beforeEach(function () {
                    $scope = $rootScope.$new();
                    $scope.cycleId = '6';

                    initController({ $scope: $scope });
                });

                it('marks the flag as such', function () {
                    expect(ctrl.edit).toBe(true);
                });

                it('waits for the data to be loaded', function () {
                    expect(ctrl.loaded.cycle).toBe(false);
                });

                it('fetches the data of the cycle with the given id', function () {
                    expect(AppraisalCycle.find).toHaveBeenCalledWith($scope.cycleId);
                });

                describe('when the model returns the data', function () {
                    beforeEach(function () {
                        $rootScope.$digest();
                    });

                    it('marks the list as loaded', function () {
                        expect(ctrl.loaded.cycle).toBe(true);
                    });
                });
            })
        });

        describe('form submit', function () {
            beforeEach(function () {
                spyOn($rootScope, '$emit');
            });

            describe('when in "create mode', function () {
                var newCycle = { name: 'The new cycle' };

                beforeEach(function () {
                    ctrl.cycle = newCycle;
                    ctrl.submit();

                    $rootScope.$digest();
                });

                it('sends a request to the api with the new cycle data', function () {
                    expect(AppraisalCycle.create).toHaveBeenCalledWith(newCycle);
                });

                it('emits an event', function () {
                    expect($rootScope.$emit).toHaveBeenCalledWith('AppraisalCycle::new', jasmine.any(Object));
                });

                it('closes the modal', function () {
                    expect($modalInstance.close).toHaveBeenCalled();
                });
            });

            describe('when in "edit mode"', function () {
                var editedCycle = { id: '657', name: 'Amended name', type: 'Amended type' };

                beforeEach(function () {
                    ctrl.edit = true;
                    ctrl.cycle = editedCycle;
                    ctrl.submit();

                    $rootScope.$digest();
                });

                it('sends a request to the api with the amended cycle data', function () {
                    expect(AppraisalCycle.update).toHaveBeenCalledWith(editedCycle.id, editedCycle);
                });

                it('emits an event', function () {
                    expect($rootScope.$emit).toHaveBeenCalledWith('AppraisalCycle::edit', jasmine.any(Object));
                });

                it('closes the modal', function () {
                    expect($modalInstance.close).toHaveBeenCalled();
                });
            });
        });

        function initController(params) {
            ctrl = $controller('AppraisalCycleModalCtrl', angular.extend({}, {
                $modalInstance: $modalInstance,
                $scope: $rootScope.$new(),
                AppraisalCycle: AppraisalCycle
            }, params));
        }
    });
});
