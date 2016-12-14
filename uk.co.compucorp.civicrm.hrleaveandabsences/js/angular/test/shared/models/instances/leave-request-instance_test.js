define([
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/models/leave-status-id-model',
  'leave-absences/shared/models/instances/leave-request-instance',
], function () {
  'use strict';

  describe('LeaveRequestInstance', function () {
    var $provide,
      LeaveRequestInstance,
      LeaveStatusID,
      LeaveRequestAPI,
      $q,
      $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.models.instances', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_) {
      //LeaveRequestAPI is internally used by Model and hence need to be mocked
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
    }));

    beforeEach(inject(function (_LeaveRequestInstance_, _LeaveRequestAPI_, _LeaveStatusID_, _$rootScope_, _$q_) {
      LeaveRequestInstance = _LeaveRequestInstance_;
      LeaveStatusID = _LeaveStatusID_;
      LeaveRequestAPI = _LeaveRequestAPI_;
      $q = _$q_;
      $rootScope = _$rootScope_;
    }));

    it('cancel() returns proper value', function () {
      var deferred = $q.defer();
      var deferred2 = $q.defer();
      var cancelledStatusId = 1;
      var firstValue = "someval";

      spyOn(LeaveStatusID, "getOptionIDByName").and.returnValue(deferred.promise);
      spyOn(LeaveRequestInstance, "update").and.returnValue(deferred2.promise);

      LeaveRequestInstance.cancel().then(function (data) {
        //check return value
        expect(data).toBe(firstValue);
      });

      deferred.resolve(cancelledStatusId);
      deferred2.resolve({
        values: [firstValue]
      });

      $rootScope.$apply();

      //check whether all functions have been called properly
      expect(LeaveStatusID.getOptionIDByName).toHaveBeenCalledWith("cancelled");
      expect(LeaveRequestInstance.update).toHaveBeenCalledWith({
        'status_id': cancelledStatusId
      });
    });

    it("update() calls proper functions and returns expected value", function () {
      var attr = {
        key: "someval"
      };
      var returnValue = "some return val";
      var sendPOSTMock = jasmine.createSpy("sendPOST");
      sendPOSTMock.and.returnValue(returnValue);
      LeaveRequestAPI.sendPOST = sendPOSTMock;

      expect(LeaveRequestInstance.update(attr)).toBe(returnValue);
      expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', _.assign(LeaveRequestInstance, attr));
    })

  });
});
