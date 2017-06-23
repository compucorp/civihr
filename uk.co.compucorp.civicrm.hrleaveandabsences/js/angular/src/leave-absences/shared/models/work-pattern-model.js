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
         *  Calls the assignWorkPattern() method of the WorkPattern API
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
         * @param  {Object} params
         * @return {Promise}
         */
        default: function (params) {
          return workPatternAPI.get(_.assign({}, params, {
            default: true
          }))
            .then(function (defaultWorkPattern) {
              return instance.init(defaultWorkPattern[0], true);
            });
        },

        /**
         * Calls the workPatternsOf() method of the WorkPattern API, and returns an
         * WorkPatternInstance for each workPattern.
         *
         * @param {string} contactId
         * @param {object} params - additional parameters
         * @return {Promise}
         */
        workPatternsOf: function (contactId, params) {
          return workPatternAPI.workPatternsOf(contactId, params)
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
