define([
  'leave-absences/shared/modules/models',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('LeaveRequest', [
    '$log', 'Model', 'LeaveRequestAPI', 'LeaveRequestInstance',
    function ($log, Model, leaveRequestAPI, instance) {

      return Model.extend({

        all: function (filters, pagination, sort, params) {
          return leaveRequestAPI.all(filters, pagination, sort, params)
            .then(function (leaveRequests) {
              return leaveRequests.map(function (leaveRequest) {
                return instance.init(leaveRequest, true);
              });
            });
        },

        balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
          return leaveRequestAPI.balanceChangeByAbsenceType(contactId, periodId, statuses, isPublicHoliday)

        }
      });
    }
  ]);
});
