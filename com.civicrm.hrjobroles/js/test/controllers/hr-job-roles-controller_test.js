define([
    'common/angularMocks',
    'job-roles/app'
], function () {
    'use strict';

    describe('HRJobRolesController', function () {
        var ctrl, scope;

        beforeEach(module('hrjobroles'));
        beforeEach(inject(function ($controller, $rootScope) {
            scope = $rootScope.$new();
            ctrl = $controller('HRJobRolesController', { $scope: scope, format: 'DD/MM/YYYY' });
        }));

        it('example', function () {
            expect(true).toBe(true);
        });

        it('Date Format should be already defined', function () {
            expect(scope.format).toBe('DD/MM/YYYY');
        });


    });
});
