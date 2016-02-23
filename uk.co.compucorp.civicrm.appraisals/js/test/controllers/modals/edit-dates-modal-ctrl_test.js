define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('EditDatesModalCtrl', function () {
        var $modalInstance, ctrl;

        beforeEach(module('appraisals', 'appraisals.templates'));
        beforeEach(inject(function ($controller, $rootScope) {
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);

            ctrl = $controller('EditDatesModalCtrl', {
                $modalInstance: $modalInstance,
                $scope: (function (scope) {
                    var modalScope = scope;
                    scope.cycle = { id: '8' };

                    return scope;
                })($rootScope.$new())
            });
        }));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        it('contains the cycle in its scope', function () {
            expect(ctrl.cycle).toBeDefined();
            expect(ctrl.cycle.id).toBe('8');
        });
    });
});
