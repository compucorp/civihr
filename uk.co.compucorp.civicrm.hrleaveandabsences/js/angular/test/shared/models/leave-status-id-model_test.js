define([
  'leave-absences/shared/models/leave-status-id-model',
  'leave-absences/shared/modules/models',
  'common/services/api/option-group',
  'common/models/model'
], function () {
  'use strict';

  describe('LeaveStatusID', function () {
    var $provide,
      OptionGroup,
      $q,
      $rootScope,
      LeaveStatusID;

    beforeEach(module('leave-absences.models', 'common.models', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject([
      'api.optionGroup',
      "LeaveStatusID",
      "$q",
      "$rootScope",
      function (_OptionGroup_, _LeaveStatusID_, _$q_, _$rootScope_) {
        OptionGroup = _OptionGroup_;
        LeaveStatusID = _LeaveStatusID_;
        $rootScope = _$rootScope_;
        $q = _$q_;
      }]));

    it('getAll to be called with right parameters and returns expected value', function () {
      spyOn(OptionGroup, "valuesOf").and.returnValue("someval");
      expect(LeaveStatusID.getAll()).toBe("someval");
      expect(OptionGroup.valuesOf).toHaveBeenCalledWith("hrleaveandabsences_leave_request_status");
    });

    it('getOptionIDByName return expected value', function () {
      var deferred = $q.defer();
      var param = {
        name: "somename"
      };
      spyOn(LeaveStatusID, "getAll").and.returnValue(deferred.promise);

      LeaveStatusID.getOptionIDByName("somename").then(function (data) {
        expect(data).toEqual(param);
      });

      deferred.resolve([param]);
      $rootScope.$apply();
    });
  });
});
