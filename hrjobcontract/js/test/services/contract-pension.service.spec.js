/* globals inject */
/* eslint-env amd, jasmine */

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

      $httpBackend.whenGET(/action=get&entity=HRJobPension/).respond(MockContract.contractPension);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('calls getOne() and return expected contract pension data', function () {
        ContractPensionService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result).toEqual(MockContract.contractPension.values[0]);
        });
      });
    });
  });
});
