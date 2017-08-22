/* eslint-env amd */

define([
  'leave-absences/shared/modules/apis',
  'mocks/data/leave-balance-report.data'
], function (apis, mockData) {
  'use strict';

  apis.factory('LeaveBalanceReportAPI', [
    '$q', '$log', 'api',
    function ($q, $log, api) {
      return api.extend({
        /**
         * Returns all records for the leave balance report.
         * @TODO remove mock resolve and use api.getAll call.
         *
         * @param {Object} filters the options to filter the results of the request.
         * @param {Object} pagination the pagination values.
         * @param {int} pagination.page the page to retrieve.
         * @param {int} pagination.size the number of records to retrieve per page.
         * @param {String} sort the sort order of the records. Uses SQL notation.
         * @return {Promise.<Object>} Resolves to an object with the values, totals, and ids list.
         */
        getAll: function (filters, pagination, sort) {
          $log.debug('LeaveBalanceReportAPI.all');

          var values = mockData.all().values;

          if (pagination) {
            var limit, offset;
            pagination.page = pagination.page || 1;
            pagination.size = pagination.size || values.length;
            offset = (pagination.page - 1) * pagination.size;
            limit = offset + pagination.size;

            values = values.slice(offset, limit);
          }

          return $q.resolve({
            list: values,
            total: mockData.all().values.length,
            allIds: values.map(function (v) { return v.id; }).join(',')
          });
        }
      });
    }
  ]);
});
