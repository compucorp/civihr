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
            toilDurationHours: 0,
            toilDurationMinutes: 0,
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
        },

        /**
         * Update duration
         */
        updateDuration: function () {
          this.duration = this.toilDurationHours * 60 + this.toilDurationMinutes;
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['toilDurationHours', 'toilDurationMinutes'], key)) {
            result[key] = this[key];
          }
        }
      });
    }
  ]);
});
