define([
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (modelInstances) {
  'use strict';

  modelInstances.factory('TOILRequestInstance', [
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function (LeaveRequestAPI, LeaveRequestInstance) {
      return LeaveRequestInstance.extend({

        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            toilDuration: 0
          };
        },

        /**
         * Create a new TOIL request
         *
         * @return {Promise} Resolved with {Object} Created Leave request with
         *  newly created id for this instance
         */
        create: function () {
          return LeaveRequestAPI.create(this.toAPI(), 'toil')
            .then(function (result) {
              this.id = result.id;
            }.bind(this));
        },

        /**
         * Validate TOIL request instance attributes.
         *
         * @return {Promise} empty array if no error found otherwise an object
         *  with is_error set and array of errors
         */
        isValid: function () {
          return LeaveRequestAPI.isValid(this.toAPI(), 'toil');
        },

        /**
         * Update a TOIL request
         *
         * @return {Promise} Resolved with {Object} Updated TOIL request
         */
        update: function () {
          return LeaveRequestAPI.update(this.toAPI(), 'toil');
        }
      });
    }
  ]);
});
