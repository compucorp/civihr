/* eslint-env amd, jasmine */

define([
  'mocks/data/contract',
  'job-contract/modules/job-contract.module'
], function (MockContract) {
  'use strict';

  describe('contractHourService', function () {
    var $httpBackend, $rootScope, contractHourService;

    beforeEach(module('job-contract'));
    beforeEach(inject(function (_contractHourService_, _$httpBackend_, _$rootScope_) {
      contractHourService = _contractHourService_;
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
        contractHourService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(MockContract.contractHour.values[0]);
        });
      });
    });
  });
});
