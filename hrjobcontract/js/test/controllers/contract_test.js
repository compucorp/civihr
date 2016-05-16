define([
    'common/angularMocks',
    'job-contract/app'
], function () {
    'use strict';

    describe('ContractCtrl', function () {
        var ctrl;

        beforeEach(module('hrjc'));
        beforeEach(inject(function ($controller, $rootScope) {
            var $scope = $rootScope.$new();
            $scope.contract = { id: '1' };

            ctrl = $controller('ContractCtrl', { $scope: $scope });
        }));

        it('example', function () {
            expect(true).toBe(true);
        });
    });
});
