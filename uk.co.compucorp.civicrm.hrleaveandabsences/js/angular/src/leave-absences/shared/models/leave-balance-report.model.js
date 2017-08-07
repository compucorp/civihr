/* eslint-env amd */

define([
  'leave-absences/shared/modules/models',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('LeaveBalanceReport', [
    '$log', 'Model', 'LeaveBalanceReportAPI',
    function ($log, Model, LeaveBalanceReportAPI) {
      return Model.extend({
        all: function (filters, pagination, sort) {
          return LeaveBalanceReportAPI.getAll(filters, pagination, sort);
        }
      });
    }
  ]);
});
