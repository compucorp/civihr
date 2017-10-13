/* eslint-env amd */

define([
  'leave-absences/shared/modules/models',
  'leave-absences/shared/apis/entitlement.api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('LeaveBalanceReport', [
    'Model', 'EntitlementAPI',
    function (Model, EntitlementAPI) {
      return Model.extend({
        all: function (filters, pagination, sort, additionalParams, cache) {
          return EntitlementAPI.getLeaveBalances(this.processFilters(filters),
            pagination, sort, additionalParams, cache);
        }
      });
    }
  ]);
});
