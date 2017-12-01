/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/contract',
  'job-contract/modules/job-contract.module'
], function (angular, MockContract) {
  'use strict';

  describe('contractLeaveService', function () {
    var $httpBackend, $rootScope, contractLeaveService;

    beforeEach(module('job-contract'));

    beforeEach(inject(function (_contractLeaveService_, _$httpBackend_, _$rootScope_) {
      contractLeaveService = _contractLeaveService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobLeave/).respond(MockContract.contractLeaves);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      var contractLeaves;

      beforeEach(function () {
        contractLeaves = angular.copy(MockContract.contractLeaves.values)
        .map(function (contract) {
          contract.add_public_holidays = contract.add_public_holidays === '1';
          return contract;
        });
      });

      it('calls getOne() and returns expected contract leaves', function () {
        contractLeaveService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(contractLeaves);
        });
      });
    });
  });
});
