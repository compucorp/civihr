define([
  'common/angular',
  'mocks/data/contract',
  'job-contract/app'
], function (angular, MockContract) {
  'use strict';

  describe('ContractRevisionList', function () {
    var $rootScope, $httpBackend, ContractRevisionList;

    beforeEach(module('hrjc'));

    beforeEach(inject(function ( _$rootScope_, _$httpBackend_, _ContractRevisionList_) {
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      ContractRevisionList = _ContractRevisionList_;

      $httpBackend.whenGET(/action=get&entity=HRJobContractRevision/).respond(MockContract.contractRevisionData);
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/list&entityID=159&entityTable=civicrm_hrjobcontract_details/).respond({});
      $httpBackend.whenGET(/action=getsingle&entity=HRJobContractRevision/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    describe('when calling fetchRevisions()', function () {

      var promise;

      beforeEach(function() {
        promise = ContractRevisionList.fetchRevisions(MockContract.contractRevisionData.values[0].id);
      });

      afterEach(function() {
        $httpBackend.flush();
        $rootScope.$apply();
      });

      it('makes http call', function () {
        $httpBackend.expectGET(/rest&action=get&entity=HRJobContractRevision/);
      });

      it('returns revisionList for a contract', function () {
        promise.then(function (result) {
          expect(result.revisionList.length).toEqual(1);
          expect(result.revisionList[0].created_date).toBe(MockContract.contractRevisionData.values[0].created_date);
          expect(result.revisionList[0].effective_date).toBe(MockContract.contractRevisionData.values[0].effective_date);
          expect(result.revisionList[0].modified_date).toBe(MockContract.contractRevisionData.values[0].modified_date);
        });
      });

      it('returns revisionDataList for a contract', function () {
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
