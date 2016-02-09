define([
    'common/angularMocks',
    'job-roles/app'
], function () {
    'use strict';

    describe('HRJobRolesController', function () {
        var ctrl;

        beforeEach(module('hrjobroles'));
        beforeEach(inject(function ($controller, $rootScope) {
            ctrl = $controller('HRJobRolesController', { $scope: $rootScope.$new(), format: 'DD/MM/YYYY' });
        }));

        it('example', function () {
            expect(true).toBe(true);
        });
    });
});
