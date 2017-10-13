/* eslint-env amd, jasmine */

define([
  'mocks/data/leave-balance-report.data',
  'leave-absences/shared/models/leave-balance-report.model'
], function (mockData) {
  'use strict';

  describe('LeaveBalanceReport', function () {
    var $provide, $rootScope, LeaveBalanceReport, EntitlementAPI;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_EntitlementAPIMock_) {
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(function (_$rootScope_, _LeaveBalanceReport_,
      _EntitlementAPI_) {
      $rootScope = _$rootScope_;
      LeaveBalanceReport = _LeaveBalanceReport_;
      EntitlementAPI = _EntitlementAPI_;
    }));

    describe('all()', function () {
      var expectedResult, result;
      var filters = { contact_id: 1 };
      var paging = { page: 1, size: 50 };
      var sort = 'contact_id ASC';

      beforeEach(function () {
        var data = mockData.all().values;

        expectedResult = {
          list: data,
          total: data.length,
          allIds: []
        };

        spyOn(EntitlementAPI, 'getLeaveBalances').and.callThrough();
        LeaveBalanceReport.all(filters, paging, sort, {}, false).then(function (value) {
          result = value;
        });
        $rootScope.$digest();
      });

      it('calls getLeaveBalances() API method of Entitlement entity', function () {
        expect(EntitlementAPI.getLeaveBalances).toHaveBeenCalledWith(
          filters, paging, sort, {}, false);
      });

      it('returns API call result', function () {
        expect(result).toEqual(expectedResult);
      });
    });
  });
});
