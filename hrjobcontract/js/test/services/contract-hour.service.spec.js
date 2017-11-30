/* globals inject */
/* eslint-env amd, jasmine */

define([
  'mocks/data/contract',
  'job-contract/app'
], function (MockContract) {
  'use strict';

  describe('ContractHourService', function () {
    var $httpBackend, $rootScope, ContractHourService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractHourService_, _$httpBackend_, _$rootScope_) {
      ContractHourService = _ContractHourService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobHour/).respond(MockContract.contractHour);
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('calls getOne() to return expected contract hour data', function () {
        ContractHourService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(MockContract.contractHour.values[0]);
        });
      });
    });
  });
});
