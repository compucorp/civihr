/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (_, apis) {
  'use strict';

  apis.factory('WorkPatternAPI', ['$log', 'api', function ($log, api) {
    $log.debug('WorkPatternAPI');

    return api.extend({

      /**
       * Assigns a work pattern to a contact
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
        return this.sendPOST('ContactWorkPattern', 'create', _.assign({}, params, {
          contact_id: contactId,
          pattern_id: workPatternID,
          effective_date: effectiveDate,
          effective_end_date: effectiveEndDate,
          change_reason: changeReason
        })).then(function (data) {
          return data.values;
        });
      },

      /**
       * Returns all the work patterns
       *
       * @param {object} params additional parameters
       * @return {Promise} Resolved with {Array} All Work Patterns
       */
      get: function (params) {
        return this.sendGET('WorkPattern', 'get', params || {})
          .then(function (data) {
            return data.values;
          });
      },

      /**
       * Returns the calendar for the given contact(s) and the date range,
       * as a list of days and their type
       *
       * @param {string/int/Array} contactId can be also an array for multiple contacts
       * @param {string} startDate
       * @param {string} endDate
       * @param {object} params additional parameters
       * @return {Promise} Resolved with {Array} All calendar records
       */
      getCalendar: function (contactId, startDate, endDate, params) {
        $log.debug('WorkPatternAPI.getCalendar', contactId, startDate, endDate, params);

        return this.sendGET('WorkPattern', 'getcalendar', _.assign({}, params, {
          contact_id: _.isArray(contactId) ? { 'IN': contactId } : contactId,
          start_date: startDate,
          end_date: endDate
        }));
      },

      /**
       * Un assign a work pattern by the given contact work pattern ID
       *
       * @param {string} contactWorkPatternID
       * @return {Promise}
       */
      unassignWorkPattern: function (contactWorkPatternID) {
        return this.sendPOST('ContactWorkPattern', 'delete', {
          id: contactWorkPatternID
        });
      },

      /**
       * Returns all the work patterns of a specific contact
       *
       * @param {string} contactId
       * @param {object} params - additional parameters
       * @param {boolean} cache
       * @return {Promise} Resolved with {Array} All Work Patterns of the contact
       */
      workPatternsOf: function (contactId, params, cache) {
        return this.sendGET('ContactWorkPattern', 'get', _.assign({}, params, {
          contact_id: contactId,
          'api.WorkPattern.get': { 'id': '$value.pattern_id' }
        }), cache).then(function (data) {
          data = data.values;

          return data.map(storeWorkPattern);
        });
      }
    });

    /**
     * ContactWorkPatterns data will have key 'api.WorkPattern.get'
     * which is normalized with a friendlier 'workPatterns' key
     *
     * @param  {Object} workPattern
     * @return {Object}
     */
    function storeWorkPattern (workPattern) {
      var clone = _.clone(workPattern);

      clone['workPattern'] = clone['api.WorkPattern.get']['values'][0];
      delete clone['api.WorkPattern.get'];

      return clone;
    }
  }]);
});
