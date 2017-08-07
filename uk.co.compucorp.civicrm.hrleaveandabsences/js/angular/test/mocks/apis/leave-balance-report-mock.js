/* eslint-env amd */

define([
  'mocks/module',
  'mocks/data/leave-balance-report-data'
], function (mocks, mockData) {
  'use strict';

  mocks.factory('LeaveBalanceReportAPIMock', [
    '$q',
    function ($q) {
      return {
        getAll: function (filters, pagination, sort) {
          var values = mockData.all().values;

          return $q.resolve({
            list: values,
            total: values.length,
            allIds: values.map(function (v) { return v.id; }).join(',')
          });
        }
      };
    }
  ]);
});
