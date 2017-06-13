define([
  'mocks/data/contract',
  'job-contract/app'
], function (MockContract) {
  'use strict';

  describe('ContractPensionService', function () {
    var $httpBackend, $rootScope, ContractPensionService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_ContractPensionService_, _$httpBackend_, _$rootScope_) {
      ContractPensionService = _ContractPensionService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(MockContract.contractPension);
      $httpBackend.whenGET(/action=get&entity=HRJobPension/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it("defines getOne() function", function () {
        expect(ContractPensionService.getOne).toBeDefined();
      });

      it('calls getOne() and return expected contract pension data', function () {
        ContractPensionService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.id).toEqual(MockContract.contractPension.values.id);
          expect(result.jobcontract_revision_id).toEqual(MockContract.contractPension.values.jobcontract_revision_id);
        });
      });
    });
  });
});
