define([
    'common/angular',
    'mocks/job-roles',
    'common/angularMocks',
    'job-roles/app'
], function (angular, Mock) {
    'use strict';

    describe('HRJobRolesController', function () {
        var ctrl, scope, DateValidation;

        beforeEach(module('hrjobroles'));
        beforeEach(inject(function ($controller, $rootScope, _DateValidation_) {
            scope = $rootScope.$new();
            ctrl = $controller('HRJobRolesController', { $scope: scope, format: 'DD/MM/YYYY' });
            DateValidation = _DateValidation_;

            // Mock data from CiviCRM settings
            DateValidation.dateFormats.push('DD/MM/YYYY');
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

        describe('Validate role', function(){
            var form_data;

            beforeEach(function(){
                form_data = angular.copy(Mock.form_data);
            });

            it('should not pass validation', function(){
                form_data.title.$viewValue = 'test';

                expect(scope.validateRole(form_data)).not.toBe(true);
            });

            it('should pass validation dd/mm/yyyy', function(){
                form_data.start_date.$viewValue = '31/12/2015';
                form_data.title.$viewValue = 'test';

                expect(scope.validateRole(form_data)).toBe(true);
            });

            it('should pass validation new Date()', function(){
                form_data.start_date.$viewValue = new Date();
                form_data.title.$viewValue = 'test';

                expect(scope.validateRole(form_data)).toBe(true);
            });

            it('should pass validation new Date()', function(){
                form_data.start_date.$viewValue = '2005-05-05';
                form_data.title.$viewValue = 'test';

                expect(scope.validateRole(form_data)).toBe(true);
            });
        });
    });
});
