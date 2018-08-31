/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/contract.data',
  'job-contract/job-contract.module'
], function (angular, MockContract) {
  'use strict';

  describe('contractRevisionListService', function () {
    var $rootScope, $httpBackend, contractFilesService, contractRevisionListService, promise;

    beforeEach(module('job-contract'));

    beforeEach(inject(function (_$rootScope_, _$httpBackend_, _contractFilesService_, _contractRevisionListService_) {
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      contractFilesService = _contractFilesService_;
      contractRevisionListService = _contractRevisionListService_;

      spyOn(contractFilesService, 'get').and.returnValue([]);

      $httpBackend.whenGET(/action=get&entity=HRJobContractRevision/).respond(MockContract.contractRevisionData);
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/list&entityID=159&entityTable=civicrm_hrjobcontract_details/).respond({});
      $httpBackend.whenGET(/action=getsingle&entity=HRJobContractRevision/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    describe('fetchRevisions()', function () {
      beforeEach(function () {
        promise = contractRevisionListService.fetchRevisions(MockContract.contractRevisionData.values[0].id);
      });

      afterEach(function () {
        $httpBackend.flush();
        $rootScope.$apply();
      });

      it('returns revision list for a given contract', function () {
        promise.then(function (result) {
          expect(result.revisionList.length).toEqual(1);
          expect(result.revisionList[0].created_date).toBe(MockContract.contractRevisionData.values[0].created_date);
          expect(result.revisionList[0].effective_date).toBe(MockContract.contractRevisionData.values[0].effective_date);
          expect(result.revisionList[0].modified_date).toBe(MockContract.contractRevisionData.values[0].modified_date);
        });
      });

      it('returns revision data list for a contract', function () {
        promise.then(function (result) {
          expect(result.revisionDataList.length).toBe(1);
          expect(result.revisionDataList[0].revisionEntityIdObj.created_date).toBe(MockContract.contractRevisionData.values[0].created_date);
          expect(result.revisionDataList[0].revisionEntityIdObj.effective_date).toBe(MockContract.contractRevisionData.values[0].effective_date);
          expect(result.revisionDataList[0].revisionEntityIdObj.modified_date).toBe(MockContract.contractRevisionData.values[0].modified_date);
        });
      });
    });
  });
});
