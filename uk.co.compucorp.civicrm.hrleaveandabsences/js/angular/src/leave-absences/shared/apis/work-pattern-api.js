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
       * This method returns the calendar for the given contact(s) and period,
       * as a list of days and their type
       *
       * @param {string/int/Array} contactId can be also an array for multiple contacts
       * @param {string/int} periodId The ID of the Absence Period
       * @param {object} params additional parameters
       * @return {Promise} Resolved with {Array} All calendar records
       */
      getCalendar: function (contactId, periodId, params) {
        $log.debug('WorkPatternAPI.getCalendar', contactId, periodId, params);

        return this.sendGET('WorkPattern', 'getcalendar',  _.assign({}, params, {
          contact_id: _.isArray(contactId) ? { "IN": contactId } : contactId,
          period_id: periodId
        }));
      }
    });
  }]);
});
