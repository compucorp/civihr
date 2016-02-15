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

        describe('$scope.parseDate()', function () {
            it('should correctly parse valid date', function () {
                // dd/mm/yyyy
                expect(scope.parseDate('01/01/2005')).toBe('2005-01-01');
                // yyyy-mm-dd
                expect(scope.parseDate('2005-01-01')).toBe('2005-01-01');
                // date object
                expect(scope.parseDate(new Date(2005, 0, 1))).toBe('2005-01-01');
                // timestamp
                expect(scope.parseDate(new Date(2005, 0, 1).getTime())).toBe('2005-01-01');
            });

            it('should not parse invalid date', function () {
                expect(scope.parseDate(null)).toBe(null);
                expect(scope.parseDate(undefined)).toBe(null);
                expect(scope.parseDate(false)).toBe(null);
            });
        });
    });
});
