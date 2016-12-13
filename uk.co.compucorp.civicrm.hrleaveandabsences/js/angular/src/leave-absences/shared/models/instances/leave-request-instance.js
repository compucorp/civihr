define([
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/leave-status-id-model',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('LeaveRequestInstance', [
    'ModelInstance',
    'LeaveRequestAPI',
    'LeaveStatusID',
    function (ModelInstance, LeaveRequestAPI, LeaveStatusID) {
      return ModelInstance.extend({

        /**
         * This method is used to cancel a leave request
         *
         * @return {Promise}
         */
        cancel: function () {
          var leaveRequest = this;
          LeaveStatusID.getOptionIDByName("cancelled")
            .then(function (cancelledStatusId) {
              return leaveRequest.update({
                'status_id': cancelledStatusId
              })
            })
            .then(function (data) {
              return data.values[0];
            });
        },

        /**
         * This method is used to cancel a leave request
         *
         * @param {object} attributes - Values which needs to be updated
         * @return {Promise}
         */
        update: function (attributes) {
          var leaveRequest = this;
          return LeaveRequestAPI.sendPOST('LeaveRequest', 'create', _.assign(leaveRequest, attributes))
        }
      });
    }]);
});
