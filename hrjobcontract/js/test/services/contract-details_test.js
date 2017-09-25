/* globals inject */
/* eslint-env amd, jasmine */

define([
  'mocks/data/contract',
  'job-contract/app'
], function (MockContract) {
  'use strict';

  describe('ContractDetailsService', function () {
    var $httpBackend, $rootScope, ContractDetailsService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractDetailsService_, _$httpBackend_, _$rootScope_) {
      ContractDetailsService = _ContractDetailsService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRJobDetails/).respond(MockContract.contract);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('calls getOne() to get expected contract data', function () {
        ContractDetailsService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(MockContract.contract.values[0]);
        });
      });
    });
  });
});
