/* eslint-env amd */

define([
  'leave-absences/shared/modules/apis'
], function (apis) {
  'use strict';

  apis.factory('LeaveBalanceReportAPI', ['$log', 'api', function ($log, api) {
    return api.extend({
      /**
       * Returns all records from the leave balance report
       *
       * @param {Object} filters the options to filter the results of the request.
       * @return {Promise}
       */
      getAll: function (filters, pagination, sort) {
        $log.debug('LeaveBalanceReportAPI.all');

        return api.getAll('LeaveBalanceReport', filters, pagination, sort);
      }
    });
  }]);
});
