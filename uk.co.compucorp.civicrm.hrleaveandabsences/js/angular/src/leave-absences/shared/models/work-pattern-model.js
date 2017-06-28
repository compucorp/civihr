/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/work-pattern-instance',
  'leave-absences/shared/apis/work-pattern-api',
  'common/models/model'
], function (_, models) {
  'use strict';

  models.factory('WorkPattern', [
    '$log', 'Model', 'WorkPatternAPI', 'WorkPatternInstance',
    function ($log, Model, workPatternAPI, instance) {
      $log.debug('WorkPattern');

      return Model.extend({

        /**
         * Assigns the given work pattern to the given contact id, also sets
         * effective date, effective end date and change reason
         *
         * @param {string} contactId
         * @param {string} workPatternID
         * @param {string} effectiveDate
         * @param {string} effectiveEndDate
         * @param {string} changeReason
         * @param {object} params - additional parameters
         * @return {Promise}
         */
        assignWorkPattern: function (contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, params) {
          return workPatternAPI.assignWorkPattern(contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, params);
        },

        /**
         * Return the default work pattern
         *
         * @return {Promise}
         */
        default: function () {
          return workPatternAPI.get({ default: true })
            .then(function (defaultWorkPattern) {
              return instance.init(defaultWorkPattern[0], true);
            });
        },

        /**
         * Unassign a work pattern by the given contact work pattern ID
         *
         * @param {string} contactWorkPatternID
         * @return {Promise}
         */
        unassignWorkPattern: function (contactWorkPatternID) {
          return workPatternAPI.unassignWorkPattern(contactWorkPatternID);
        },

        /**
         * Returns the work patterns of the contact with the given id
         *
         * @param {string} contactId
         * @param {object} params - additional parameters
         * @param {boolean} cache
         * @return {Promise}
         */
        workPatternsOf: function (contactId, params, cache) {
          return workPatternAPI.workPatternsOf(contactId, params, cache)
            .then(function (workPatterns) {
              return workPatterns.map(function (workPattern) {
                return instance.init(workPattern, true);
              });
            });
        }
      });
    }
  ]);
});
