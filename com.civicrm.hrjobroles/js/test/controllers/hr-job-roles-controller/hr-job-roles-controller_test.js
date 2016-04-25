define([
    'common/angular',
    'common/lodash',
    'mocks/job-roles',
    'common/angularMocks',
    'job-roles/app'
], function (angular, _, Mock) {
    'use strict';

    describe('HRJobRolesController', function () {
        var $controller, $q, $rootScope, DateValidation, HRJobRolesService, ctrl, scope;
        var contactId = '123';

        beforeEach(module('hrjobroles'));
        beforeEach(inject(function ($httpBackend, _$controller_, _$q_, _$rootScope_, _DateValidation_, _HRJobRolesService_) {
            $controller = _$controller_;
            $q = _$q_;
            $rootScope = _$rootScope_;

            DateValidation = _DateValidation_;
            HRJobRolesService = _HRJobRolesService_;

            // Ignores any request for templates
            $httpBackend.expectGET(/\/views\//).respond(200);

            // Mock data from CiviCRM settings
            DateValidation.dateFormats.push('DD/MM/YYYY');
        }));

        describe('getOptionValues', function() {
          beforeEach(function () {
            spyOn(HRJobRolesService, 'getOptionValues').and.returnValue($q.resolve({
               "count":5,
               "values":[
                  {
                     "id":"845",
                     "option_group_id":"111",
                     "label":"Senior Manager",
                     "value":"Senior Manager",
                     "name":"Senior_Manager",
                     "is_default":"0",
                     "weight":"1",
                     "is_optgroup":"0",
                     "is_reserved":"0",
                     "is_active":"1"
                  },
                  {
                     "id":"846",
                     "option_group_id":"111",
                     "label":"Junior Manager",
                     "value":"Junior Manager",
                     "name":"Junior_Manager",
                     "is_default":"0",
                     "weight":"2",
                     "is_optgroup":"0",
                     "is_reserved":"0",
                     "is_active":"1"
                  },
                  {
                     "id":"879",
                     "option_group_id":"124",
                     "label":"Other",
                     "value":"Other",
                     "name":"Other",
                     "filter":"0",
                     "is_default":"0",
                     "weight":"3",
                     "is_optgroup":"0",
                     "is_reserved":"0",
                     "is_active":"1"
                  },
                  {
                     "id":"1045",
                     "option_group_id":"124",
                     "label":"Test A",
                     "value":"1",
                     "name":"Test A",
                     "filter":"0",
                     "is_default":"0",
                     "weight":"2",
                     "is_optgroup":"0",
                     "is_reserved":"0",
                     "is_active":"1"
                  },
                  {
                     "id":"1046",
                     "option_group_id":"124",
                     "label":"Test B",
                     "value":"2",
                     "name":"Test B",
                     "filter":"0",
                     "is_default":"0",
                     "weight":"1",
                     "description":"Test B",
                     "is_optgroup":"0",
                     "is_reserved":"0",
                     "is_active":"1"
                  }
               ],
               "optionGroupData":{
                  "cost_centres":"124"
               }
            }));
            initController();
            $rootScope.$digest();
          });

          it('builds the "CostCentreList" array of objects containing the "weight" property', function(){
            expect(scope.CostCentreList.length).toBe(3);
            expect(scope.CostCentreList[0].weight).not.toBeNull();
            expect(scope.CostCentreList[1].weight).not.toBeNull();
            expect(scope.CostCentreList[2].weight).not.toBeNull();
          });
        });

        describe('Basic tests', function () {
            beforeEach(function () {
                spyOn(HRJobRolesService, 'getContracts').and.callThrough();

                initController();
            });

            describe('on init', function () {
                it('fetches the Job Contract of the contact', function () {
                    expect(HRJobRolesService.getContracts).toHaveBeenCalledWith(contactId);
                });
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

            describe('Fetching Dates from contract', function () {
                beforeEach(function () {
                    ctrl.contractsData = angular.copy(Mock.contracts_data);
                });

                describe('Checking if dates entered in job role are th same as those in contracts', function () {

                    it('should check if entered dates are custom', function () {
                        expect(scope.checkIfDatesAreCustom('2005-01-01', null)).toBe(true);
                    });

                    it('should omit a time information', function () {
                        expect(scope.checkIfDatesAreCustom(Mock.contracts_data[0].start_date + ' 00:00:00', Mock.contracts_data[0].end_date)).toBe(false);
                    });

                    it('should successfully compare dates to contract without end date', function(){
                        expect(scope.checkIfDatesAreCustom(Mock.contracts_data[2].start_date, null)).toBe(false);
                    });

                    it('should successfully compare date object', function(){
                        expect(scope.checkIfDatesAreCustom(new Date(2016, 0, 1), new Date(2016, 0, 31))).toBe(false);
                    });

                    it('should return false only if both dates match the same contract', function () {
                        expect(scope.checkIfDatesAreCustom(Mock.contracts_data[0].start_date, Mock.contracts_data[0].end_date)).toBe(false);
                        expect(scope.checkIfDatesAreCustom(Mock.contracts_data[1].start_date, Mock.contracts_data[0].end_date)).toBe(true);
                    });
                });

                describe('New Job Role', function () {
                    beforeEach(function () {
                        scope.edit_data['new_role_id'] = angular.copy(Mock.new_role);
                    });

                    it('should set dates', function () {
                        scope.edit_data['new_role_id'].job_contract_id = 0;
                        scope.onContractSelected();
                        expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[0].start_date);
                        expect(scope.edit_data['new_role_id'].newEndDate).toBe(Mock.contracts_data[0].end_date);
                    });

                    it('should not modify if dates were edited manually', function () {
                        scope.edit_data['new_role_id'].newStartDate = '2005-01-01';
                        scope.edit_data['new_role_id'].job_contract_id = 1;
                        scope.onContractSelected();
                        expect(scope.edit_data['new_role_id'].newStartDate).toBe('2005-01-01');
                        expect(scope.edit_data['new_role_id'].newEndDate).toBe(null);
                    });

                    it('should set only start date if contract has no end date', function () {
                        scope.edit_data['new_role_id'].job_contract_id = 2;
                        scope.onContractSelected();
                        expect(scope.edit_data['new_role_id'].newStartDate).toBe(Mock.contracts_data[2].start_date);
                        expect(scope.edit_data['new_role_id'].newEndDate).toBe(null);
                    });

                    it('should change dates whenever contract change', function () {
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

                    it('form should be validated', function () {
                        scope.edit_data['new_role_id'].job_contract_id = 2;
                        scope.onContractSelected();

                    });
                });

                describe('Existing Job Role', function () {
                    beforeEach(function () {
                        scope.edit_data = angular.copy(Mock.roles_data);
                        ctrl.contractsData = angular.copy(Mock.contracts_data);
                    });

                    it('should set dates', function(){
                        scope.edit_data[0].start_date = null;
                        scope.edit_data[0].end_date = null;
                        scope.edit_data[0].job_contract_id = 0;
                        scope.onContractEdited(0, 0);

                        expect(scope.edit_data[0].start_date).toBe(Mock.contracts_data[0].start_date);
                        expect(scope.edit_data[0].end_date).toBe(Mock.contracts_data[0].end_date);
                    });

                    it('should not modify if dates were edited manually', function(){
                        scope.edit_data[2].start_date = '2005-01-01';
                        scope.edit_data[2].job_contract_id = 1;
                        scope.onContractEdited(1, 2);

                        expect(scope.edit_data[2].start_date).toBe('2005-01-01');
                        expect(scope.edit_data[2].end_date).toBe(Mock.contracts_data[3].end_date);
                    });

                    it('should set only start date if contract has no end date', function(){
                        scope.edit_data[2].start_date = Mock.contracts_data[1].start_date;
                        scope.edit_data[2].end_date = Mock.contracts_data[1].end_date;

                        scope.edit_data[2].job_contract_id = 2;
                        scope.onContractEdited(2, 2);
                        expect(scope.edit_data[2].start_date).toBe(Mock.contracts_data[2].start_date);
                        expect(scope.edit_data[2].end_date).toBe(null);
                    });

                    it('should change dates whenever contract change', function(){
                        scope.edit_data[2].start_date = Mock.contracts_data[1].start_date;
                        scope.edit_data[2].end_date = Mock.contracts_data[1].end_date;

                        scope.edit_data[2].job_contract_id = 0;
                        scope.onContractEdited(0, 2);
                        expect(scope.edit_data[2].start_date).toBe(Mock.contracts_data[0].start_date);
                        expect(scope.edit_data[2].end_date).toBe(Mock.contracts_data[0].end_date);

                        // change contract
                        scope.edit_data[2].job_contract_id = 1;
                        scope.onContractEdited(1, 2);
                        expect(scope.edit_data[2].start_date).toBe(Mock.contracts_data[1].start_date);
                        expect(scope.edit_data[2].end_date).toBe(Mock.contracts_data[1].end_date);

                        // change contract
                        scope.edit_data[2].job_contract_id = 2;
                        scope.onContractEdited(2, 2);
                        expect(scope.edit_data[2].start_date).toBe(Mock.contracts_data[2].start_date);
                        expect(scope.edit_data[2].end_date).toBe(null);

                        // change contract
                        scope.edit_data[2].job_contract_id = 0;
                        scope.onContractEdited(0, 2);
                        expect(scope.edit_data[2].start_date).toBe(Mock.contracts_data[0].start_date);
                        expect(scope.edit_data[2].end_date).toBe(Mock.contracts_data[0].end_date);
                    });
                });

                describe('Updating old revision dates', function(){

                });
            });

        });

        // Tests that needs to have control over the state prior
        // to the controller initialization
        describe('Initial state dependent tests', function () {
            describe('Fetching Job Roles from contract', function () {
                var spy;

                beforeEach(function () {
                    spy = spyOn(HRJobRolesService, 'getContracts');
                    spyOn(HRJobRolesService, 'getAllJobRoles').and.callThrough();
                });

                describe('when the user does not have a contract', function () {
                    beforeEach(function () {
                        spy.and.callFake(function () {
                            return fakeContractResponse([]);
                        })

                        initController();
                        $rootScope.$digest();
                    });

                    it('does not try to fetch any job role', function () {
                        expect(HRJobRolesService.getAllJobRoles).not.toHaveBeenCalled();
                    });

                    it('shows a message', function () {
                        expect(ctrl.empty).toEqual(jasmine.any(String));
                    });
                });

                describe('when the user does have a contract', function () {
                    var contracts;

                    beforeEach(function () {
                        contracts = _.toArray(angular.copy(Mock.contracts_data));

                        spy.and.callFake(function () {
                            return fakeContractResponse(contracts);
                        });

                        initController();
                        $rootScope.$digest();
                    });

                    it('fetches the job roles', function () {
                        expect(HRJobRolesService.getAllJobRoles).toHaveBeenCalledWith(contracts.map(function (contract) {
                            return contract.id;
                        }));
                    });

                    it('does not show any message', function () {
                        expect(ctrl.empty).not.toEqual(jasmine.any(String));
                    });
                });
            });

            /**
             * Fakes the response that HRJobRolesService.getContracts() would get
             *
             * @param {Array} contracts
             * @return {Promise} resolves to the response
             */
            function fakeContractResponse(contracts) {
                var deferred = $q.defer();
                deferred.resolve({ count: contracts.length, values: contracts });

                return deferred.promise;
            }
        });

        /**
         * Initializes the controller
         *
         * Sets the contact id on the fake parent ctrl
         */
        function initController() {
            scope = $rootScope.$new();
            scope.$parent.contactId = contactId;

            ctrl = $controller('HRJobRolesController', { $scope: scope, format: 'DD/MM/YYYY' });
        }
    });
});
