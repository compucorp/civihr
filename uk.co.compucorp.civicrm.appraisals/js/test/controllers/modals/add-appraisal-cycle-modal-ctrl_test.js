define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('AddAppraisalCycleModalCtrl', function () {
        var $q, $modalInstance, $rootScope, AppraisalCycle, ctrl;

        beforeEach(module('appraisals'));
        beforeEach(inject(function ($controller, _$q_, _$rootScope_, _AppraisalCycle_) {
            $q = _$q_;
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);
            $rootScope = _$rootScope_;
            AppraisalCycle = _AppraisalCycle_;

            ctrl = $controller('AddAppraisalCycleModalCtrl', {
                $modalInstance: $modalInstance,
                types: ['Type 1', 'Type 2']
            });
        }));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('init', function () {
            it('has an empty object as the new cycle', function () {
                expect(ctrl.newCycle).toEqual({});
            });

            it('receives and stores the appraisal cycle types list', function () {
                expect(ctrl.types).toEqual(['Type 1', 'Type 2']);
            });
        });

        describe('form submit', function () {
            var newCycle = { name: 'The new cycle' };

            beforeEach(function () {
                spyOn($rootScope, '$emit');
                spyOn(AppraisalCycle, 'create').and.callFake(function (value) {
                    var deferred = $q.defer();
                    deferred.resolve(value);

                    return deferred.promise;
                });

                ctrl.newCycle = newCycle;
                ctrl.addCycle();

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
    });
});
