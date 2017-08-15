/* eslint-env amd */

define([
  'leave-absences/shared/modules/models',
  'leave-absences/shared/apis/leave-balance-report.api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('LeaveBalanceReport', [
    'Model', 'LeaveBalanceReportAPI',
    function (Model, LeaveBalanceReportAPI) {
      return Model.extend({
        all: function (filters, pagination, sort) {
          return LeaveBalanceReportAPI.getAll(filters, pagination, sort);
        }
      });
    }
  ]);
});
