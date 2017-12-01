/* eslint-env amd, jasmine */

define([
  'mocks/data/contract',
  'job-contract/modules/job-contract.module'
], function (MockContract) {
  'use strict';

  describe('contractPensionService', function () {
    var $httpBackend, $rootScope, contractPensionService;

    beforeEach(module('job-contract'));

    beforeEach(inject(function (_contractPensionService_, _$httpBackend_, _$rootScope_) {
      contractPensionService = _contractPensionService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobPension/).respond(MockContract.contractPension);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('calls getOne() and return expected contract pension data', function () {
        contractPensionService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result).toEqual(MockContract.contractPension.values[0]);
        });
      });
    });
  });
});
