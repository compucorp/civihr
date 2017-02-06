define([
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (modelInstances) {
  'use strict';

  modelInstances.factory('SicknessRequestInstance', [
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function (LeaveRequestAPI, LeaveRequestInstance) {
      var sicknessInstance = Object.create(LeaveRequestInstance);

      /**
       * Create a new sickness request
       *
       * @return {Promise} Resolved with {Object} Created Leave request with
       *  newly created id for this instance
       */
      sicknessInstance.create = function () {
        return LeaveRequestAPI.create(this.toAPI(), 'sick')
          .then(function (result) {
            this.id = result.id;
          }.bind(this));
      };

      /**
       * Validate sickness request instance attributes.
       *
       * @return {Promise} empty array if no error found otherwise an object
       *  with is_error set and array of errors
       */
      sicknessInstance.isValid = function () {
        return LeaveRequestAPI.isValid(this.toAPI(), 'sick');
      };

      /**
       * Update a sickness request
       *
       * @return {Promise} Resolved with {Object} Updated sickness request
       */
      sicknessInstance.update = function () {
        return LeaveRequestAPI.update(this.toAPI(), 'sick');
      };

      return sicknessInstance;
    }
  ]);
});
