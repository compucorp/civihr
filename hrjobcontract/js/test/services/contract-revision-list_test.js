define([
  'common/angular',
  'job-contract/app'
], function (angular) {
  'use strict';

  describe('ContractRevisionList', function () {
    var $rootScope, $httpBackend, ContractRevisionList, responseData;

    beforeEach(module('hrjc'));

    beforeEach(inject(function ( _$rootScope_, _$httpBackend_, _ContractRevisionList_) {
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      ContractRevisionList = _ContractRevisionList_;

      responseData = {
        "is_error": 0,
        "version": 3,
        "count": 1,
        "id": 159,
        "values": [
          {
            "id": "159",
            "jobcontract_id": "94",
            "editor_uid": "1",
            "created_date": "2017-02-14 04:30:29",
            "effective_date": "2017-02-13",
            "modified_date": "2017-02-14 04:30:31",
            "details_revision_id": "159",
            "health_revision_id": "159",
            "hour_revision_id": "159",
            "leave_revision_id": "159",
            "pay_revision_id": "159",
            "pension_revision_id": "159",
            "role_revision_id": "159",
            "deleted": "0",
            "editor_name": "admin@example.com"
          }
        ]
      };

      // Mocking http calls
      $httpBackend.whenGET(/action=get&entity=HRJobContractRevision/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/list&entityID=159&entityTable=civicrm_hrjobcontract_details/).respond({});
      $httpBackend.whenGET(/action=getsingle&entity=HRJobContractRevision/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    describe('when calling fetchRevisions()', function () {

      var promise;

      beforeEach(function() {
        promise = ContractRevisionList.fetchRevisions(responseData.values[0].id);
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
          expect(result.revisionList[0].created_date).toBe(responseData.values[0].created_date);
          expect(result.revisionList[0].effective_date).toBe(responseData.values[0].effective_date);
          expect(result.revisionList[0].modified_date).toBe(responseData.values[0].modified_date);
        });
      });

      it('returns revisionDataList for a contract', function () {
        promise.then(function (result) {
          expect(result.revisionDataList.length).toBe(1);
          expect(result.revisionDataList[0].revisionEntityIdObj.created_date).toBe(responseData.values[0].created_date);
          expect(result.revisionDataList[0].revisionEntityIdObj.effective_date).toBe(responseData.values[0].effective_date);
          expect(result.revisionDataList[0].revisionEntityIdObj.modified_date).toBe(responseData.values[0].modified_date);
        });
      });
    });
  });
});
