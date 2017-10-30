/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/contract',
  'job-contract/app'
], function (angular, contractMock) {
  'use strict';

  describe('ContractLeaveService', function () {
    var $httpBackend, $q, $rootScope, AbsenceType, Contract, ContractService;
    var calculationUnitsMock = [{ value: 1, name: 'days' }, { value: 2, name: 'hours' }];
    var absenceTypesMock = _.map(contractMock.contractEntity.leave, function (leave, index) {
      return { id: leave.leave_type, calculation_unit: _.sample(calculationUnitsMock).value };
    });

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_$httpBackend_, _$q_, _$rootScope_, _AbsenceType_, _Contract_, _ContractService_) {
      $httpBackend = _$httpBackend_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      Contract = _Contract_;
      ContractService = _ContractService_;

      mockBackendCalls();
      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(AbsenceType, 'loadCalculationUnits').and.callThrough();
      spyOn(Contract, 'get').and.callFake(function () { return $q.resolve(contractMock.details); });
    }));

    describe('fullDetails()', function () {
      var contractId = 1;

      beforeEach(function () {
        ContractService.fullDetails(contractId);
        $httpBackend.flush();
        $rootScope.$digest();
      });

      it('loads absence types', function () {
        expect(AbsenceType.all).toHaveBeenCalledWith();
      });

      it('populates absence types with calculation units', function () {
        expect(AbsenceType.loadCalculationUnits).toHaveBeenCalled();
      });

      it('gets contract full details', function () {
        expect(Contract.get).toHaveBeenCalledWith({
          action: 'getfulldetails',
          json: {
            jobcontract_id: contractId
          }
        }, jasmine.any(Function), jasmine.any(Function));
      });
    });

    /**
     * Mocks back-end API calls
     */
    function mockBackendCalls () {
      $httpBackend.whenGET(/action=get&entity=HRJobLeave/).respond(contractMock.contractLeaves);
      $httpBackend.whenGET(/views.*/).respond({});
      // @NOTE This is a temporary solution until we can import mocks
      // from other extensions such as Leave and Absence extension
      $httpBackend.whenGET(/action=get&entity=AbsenceType/).respond({ 'values': absenceTypesMock });
      $httpBackend.whenGET(/action=get&entity=OptionValue/).respond({ 'values': calculationUnitsMock });
    }
  });
});
