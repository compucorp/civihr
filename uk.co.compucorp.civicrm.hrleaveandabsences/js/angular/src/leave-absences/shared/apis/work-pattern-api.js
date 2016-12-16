define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('WorkPatternAPI', ['$log', 'api', function ($log, api) {
    $log.debug('WorkPatternAPI');

    return api.extend({

      /**
       * This method returns the calendar for a specific period, as a list of days and their type
       *
       * @param {string} contactId The ID of the Contact
       * @param {string} periodId The ID of the Absence Period
       * @param {object} params
       * @return {Promise} Resolved with {Array} All calender records
       */
      getCalendar: function (contactId, periodId, params) {
        $log.debug('WorkPatternAPI.getCalendar');

        return this.sendGET('WorkPattern', 'getcalendar', params);
      }
    });
  }]);
});
