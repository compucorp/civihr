/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/leave-balance-report.data',
  'leave-absences/shared/apis/leave-balance-report.api'
], function (angular, balanceReportMockData) {
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

      describe('when filtering absence types', function () {
        var expected, result;
        var absenceType = '2';

        beforeEach(function () {
          var filteredReport, report;

          report = balanceReportMockData.all().values;

          filteredReport = report.map(function (contact) {
            contact = angular.copy(contact);
            contact.absence_types = contact.absence_types.filter(function (type) {
              return type.id === absenceType;
            });
            return contact;
          });

          expected = {
            list: filteredReport,
            total: report.length,
            allIds: jasmine.any(String)
          };

          LeaveBalanceReportAPI.getAll({ absence_type: absenceType })
          .then(function (values) {
            result = values;
          });
          $rootScope.$digest();
        });

        it('should return a filtered list of leave balances by absence type', function () {
          expect(result).toEqual(expected);
        });
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
