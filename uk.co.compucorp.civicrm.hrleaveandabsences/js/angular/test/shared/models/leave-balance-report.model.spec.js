/* eslint-env amd, jasmine */

define([
  'mocks/data/leave-balance-report-data',
  'leave-absences/shared/models/leave-balance-report.model',
  'mocks/apis/leave-balance-report-mock'
], function (mockData) {
  'use strict';

  describe('LeaveBalanceReport', function () {
    var $provide, $rootScope, LeaveBalanceReport, LeaveBalanceReportAPIMock;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveBalanceReportAPIMock_) {
      LeaveBalanceReportAPIMock = _LeaveBalanceReportAPIMock_;

      $provide.value('LeaveBalanceReportAPI', LeaveBalanceReportAPIMock);
    }));

    beforeEach(inject(function (_$rootScope_, _LeaveBalanceReport_) {
      $rootScope = _$rootScope_;
      LeaveBalanceReport = _LeaveBalanceReport_;
    }));

    describe('.all()', function () {
      var expected, result;
      var filters = { contact_id: 1 };
      var paging = { page: 1, size: 50 };
      var sort = 'contact_id ASC';

      beforeEach(function () {
        var data = mockData.all().values;
        expected = {
          list: data,
          total: data.length,
          allIds: jasmine.any(String)
        };

        spyOn(LeaveBalanceReportAPIMock, 'getAll').and.callThrough();

        LeaveBalanceReport.all(filters, paging, sort).then(function (value) {
          result = value;
        });
        $rootScope.$digest();
      });

      it('calls equivalent API method', function () {
        expect(LeaveBalanceReportAPIMock.getAll).toHaveBeenCalledWith(filters, paging, sort);
      });

      it('returns API call result', function () {
        expect(result).toEqual(expected);
      });
    });
  });
});
