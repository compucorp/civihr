define([
    'common/angular',
    'mocks/job-roles',
    'common/angularMocks',
    'job-roles/app'
], function (angular, Mock) {
    'use strict';

    describe('Fetching Dates from contract', function () {
        var ctrl, scope;

        beforeEach(module('hrjobroles'));
        beforeEach(inject(function ($controller, $rootScope) {
            scope = $rootScope.$new();
            ctrl = $controller('HRJobRolesController', { $scope: scope, format: 'DD/MM/YYYY' });
        }));

        describe('Dates are not set.', function () {
            beforeEach(function () {
                scope.edit_data['new_role_id'] = angular.copy(Mock.new_role);
                ctrl.contractsData = angular.copy(Mock.contracts_data);
            });

            it('should set dates', function(){
                scope.edit_data['new_role_id'].job_contract_id = 0;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[0].start_date);
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(Mock.contracts_data[0].end_date);
            });

            it('should check if entered dates are custom', function(){
                expect(scope.checkIfDatesAreCustom('2005-01-01', null)).toBe(true);

                // return false only if both dates match the same contract
                expect(scope.checkIfDatesAreCustom(Mock.contracts_data[0].start_date, Mock.contracts_data[0].end_date)).toBe(false);
                expect(scope.checkIfDatesAreCustom(Mock.contracts_data[1].start_date, Mock.contracts_data[0].end_date)).toBe(true);
            });

            it('should not modify if dates were edited manually', function(){
                scope.edit_data['new_role_id'].newStartDate = '2005-01-01';
                scope.edit_data['new_role_id'].job_contract_id = 1;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe('2005-01-01');
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(null);
            });

            it('should set only start date if contract has no end date', function(){
                scope.edit_data['new_role_id'].job_contract_id = 2;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[2].start_date);
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(null);
            });

            it('should change dates whenever contract change', function(){
                scope.edit_data['new_role_id'].job_contract_id = 0;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[0].start_date);
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(Mock.contracts_data[0].end_date);

                // change contract
                scope.edit_data['new_role_id'].job_contract_id = 1;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[1].start_date);
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(Mock.contracts_data[1].end_date);

                // change contract
                scope.edit_data['new_role_id'].job_contract_id = 2;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[2].start_date);
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(null);

                // change contract
                scope.edit_data['new_role_id'].job_contract_id = 0;
                scope.onContractSelected();
                expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[0].start_date);
                expect(scope.edit_data['new_role_id'].newEndDate).toBe(Mock.contracts_data[0].end_date);
            });

            it('form should be validated', function(){
                scope.edit_data['new_role_id'].job_contract_id = 2;
                scope.onContractSelected();

            });
        });
    });
});
