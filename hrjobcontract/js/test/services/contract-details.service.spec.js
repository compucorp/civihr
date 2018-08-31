/* eslint-env amd, jasmine */

define([
  'mocks/data/contract.data',
  'job-contract/job-contract.module'
], function (MockContract) {
  'use strict';

  describe('contractDetailsService', function () {
    var $httpBackend, $rootScope, contractDetailsService;

    beforeEach(module('job-contract'));
    beforeEach(inject(function (_contractDetailsService_, _$httpBackend_, _$rootScope_) {
      contractDetailsService = _contractDetailsService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(200);
      $httpBackend.whenGET(/action=get&entity=HRJobDetails/).respond(MockContract.contract);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('calls getOne() to get expected contract data', function () {
        contractDetailsService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(MockContract.contract.values[0]);
        });
      });
    });
  });
});
