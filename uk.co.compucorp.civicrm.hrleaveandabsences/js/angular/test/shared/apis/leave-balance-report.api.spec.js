/* eslint-env amd, jasmine */

define([
  'mocks/data/leave-balance-report.data',
  'leave-absences/shared/apis/leave-balance-report.api'
], function (balanceReportMockData) {
  describe('LeaveBalanceReportAPI', function () {
    var $rootScope, LeaveBalanceReportAPI;

    beforeEach(module('leave-absences.apis'));
    beforeEach(inject(function (_$rootScope_, _LeaveBalanceReportAPI_) {
      $rootScope = _$rootScope_;
      LeaveBalanceReportAPI = _LeaveBalanceReportAPI_;
    }));

    describe('getAll()', function () {
      var expected, result;

      beforeEach(function () {
        var report = balanceReportMockData.all().values;
        expected = {
          list: report,
          total: report.length,
          allIds: report.map(function (r) { return r.id; }).join(',')
        };

        LeaveBalanceReportAPI.getAll().then(function (value) {
          result = value;
        });
        $rootScope.$digest();
      });

      it('returns a list of records from the report', function () {
        expect(result).toEqual(expected);
      });

      describe('when passing paging parameter', function () {
        var expected, result;
        var paging = { page: 2, size: 3 };

        beforeEach(function () {
          var report = balanceReportMockData.all().values;

          expected = {
            list: report.slice(3, 6),
            total: report.length,
            allIds: jasmine.any(String)
          };

          LeaveBalanceReportAPI.getAll({}, paging).then(function (values) {
            result = values;
          });
          $rootScope.$digest();
        });

        it('returns a limited list of records starting from offset', function () {
          expect(result).toEqual(expected);
        });
      });
    });
  });
});
