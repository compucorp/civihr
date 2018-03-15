/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/instances/leave-request.instance'
], function (_, modelInstances) {
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
            from_date_amount: 0,
            to_date_amount: 0,
            request_type: 'toil'
          };

          return _.assign({}, LeaveRequestInstance.defaultCustomData(), toilCustomData);
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'files', 'toilDurationMinutes'], key)) {
            result[key] = this[key];
          }
        }
      });
    }
  ]);
});
