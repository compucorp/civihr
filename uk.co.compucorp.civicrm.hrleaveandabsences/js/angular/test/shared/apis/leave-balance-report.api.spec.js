/* eslint-env amd, jasmine */

define([
  'mocks/data/leave-balance-report-data',
  'leave-absences/shared/apis/leave-balance-report.api'
], function (balanceReportMockData) {
  describe('LeaveBalanceReportAPI', function () {
    var $httpBackend, LeaveBalanceReportAPI;

    beforeEach(module('leave-absences.apis'));
    beforeEach(inject(function (_$httpBackend_, $q, _LeaveBalanceReportAPI_) {
      $httpBackend = _$httpBackend_;
      LeaveBalanceReportAPI = _LeaveBalanceReportAPI_;

      interceptHttp();
    }));

    describe('.getAll()', function () {
      describe('when calling .getAll()', function () {
        var expected, result;

        beforeEach(function () {
          var values = balanceReportMockData.all().values;
          expected = {
            list: values,
            total: values.length,
            allIds: jasmine.any(String)
          };

          LeaveBalanceReportAPI.getAll().then(function (values) {
            result = values;
          });
          $httpBackend.flush();
        });

        it('returns a list of records from the report', function () {
          expect(result).toEqual(expected);
        });
      });

      describe('when passing paging parameter', function () {
        var expected, result;
        var paging = { page: 2, size: 3 };

        beforeEach(function () {
          expected = {
            list: balanceReportMockData.limit(3, 3).values,
            total: balanceReportMockData.all().values.length,
            allIds: jasmine.any(String)
          };

          LeaveBalanceReportAPI.getAll({}, paging).then(function (values) {
            result = values;
          });
          $httpBackend.flush();
        });

        it('returns a limited list of records starting from offset', function () {
          expect(result).toEqual(expected);
        });
      });
    });

    /**
     * Intercept HTTP requests and returns mocked values.
     * When limit and or offset params are passed it limits the
     * mock results accordingly.
     */
    function interceptHttp () {
      $httpBackend.whenGET(/action=get&entity=LeaveBalanceReport/)
      .respond(function (method, url, data, headers, params) {
        var json = params.json && JSON.parse(params.json);
        if (json && json.options) {
          return [
            200,
            balanceReportMockData.limit(json.options.limit, json.options.offset)
          ];
        }

        return [200, balanceReportMockData.all()];
      });
    }
  });
});
