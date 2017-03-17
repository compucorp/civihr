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
          var toilCustomData = {
            toilDurationHours: 0,
            toilDurationMinutes: 0,
            request_type: 'toil'
          };

          return _.assign({}, LeaveRequestInstance.defaultCustomData(), toilCustomData);
        },

        /**
         * Sets the duration hours and minutes from toil_duration on instantiation.
         *
         * @param {Object} attributes that need to be transformed
         * @return {Object} updated attributes object
         */
        transformAttributes: function (attributes) {
          var duration = Number(attributes.toil_duration);
          if (duration) {
            attributes.toilDurationHours = Math.floor(duration / 60).toString();
            attributes.toilDurationMinutes = (duration % 60).toString();
          }

          return attributes;
        },

        /**
         * Update duration
         */
        updateDuration: function () {
          this.toil_duration = Number(this.toilDurationHours) * 60 + Number(this.toilDurationMinutes);
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['toilDurationHours', 'toilDurationMinutes', 'comments', 'uploader'], key)) {
            result[key] = this[key];
          }
        }
      });
    }
  ]);
});
