/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models',
  'common/models/model',
  'leave-absences/shared/apis/work-pattern.api',
  'leave-absences/shared/instances/work-pattern.instance'
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
         * @param  {String} contactId
         * @param  {String} workPatternID
         * @param  {String} effectiveDate
         * @param  {String} effectiveEndDate
         * @param  {String} changeReason
         * @param  {Object} params - additional parameters
         * @return {Promise}
         */
        assignWorkPattern: function (contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, params) {
          return workPatternAPI.assignWorkPattern(contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, params);
        },

        /**
         * Gets the default work pattern
         *
         * @return {Promise} resolved with an instance of the default work pattern
         */
        default: function () {
          return workPatternAPI.get({ is_default: true })
            .then(function (defaultWorkPattern) {
              return instance.init(_.first(defaultWorkPattern), true);
            });
        },

        /**
         * Unassign a work pattern by the given contact work pattern ID
         *
         * @param  {String} contactWorkPatternID
         * @return {Promise}
         */
        unassignWorkPattern: function (contactWorkPatternID) {
          return workPatternAPI.unassignWorkPattern(contactWorkPatternID);
        },

        /**
         * Gets work patterns of the contact with the given ID
         *
         * @param  {String} contactId
         * @param  {Object} params - additional parameters
         * @param  {Boolean} cache
         * @return {Promise} resolved with a collection of instances of work patterns
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
