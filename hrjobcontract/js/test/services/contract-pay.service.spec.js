/* eslint-env amd, jasmine */

define([
  'mocks/data/contract.data',
  'job-contract/job-contract.module'
], function (MockContract) {
  'use strict';

  describe('contractPayService', function () {
    var $httpBackend, $rootScope, contractPayService;

    beforeEach(module('job-contract'));
    beforeEach(inject(function (_contractPayService_, _$httpBackend_, _$rootScope_) {
      contractPayService = _contractPayService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(200);
      $httpBackend.whenGET(/action=get&entity=HRJobPay/).respond(MockContract.contractPayment);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('calls getOne() and returns expected contract payment data', function () {
        contractPayService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(MockContract.contractPayment.values[0]);
        });
      });
    });
  });
});
