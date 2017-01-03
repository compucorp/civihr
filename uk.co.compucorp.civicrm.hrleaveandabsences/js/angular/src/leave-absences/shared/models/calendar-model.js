define([
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/calendar-instance',
  'leave-absences/shared/apis/work-pattern-api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Calendar', [
    '$log',
    'Model',
    'WorkPatternAPI',
    'CalendarInstance',
    function ($log, Model, workPatternAPI, instance) {
      $log.debug('Calendar');
      return Model.extend({

        /**
         * This method returns the calendar for a specific period, as a list of days and their type
         *
         * @param {string} contactId The ID of the Contact
         * @param {string} periodId The ID of the Absence Period
         * @param {object} params
         * @return {Promise} Resolved with {Object} Calendar Instance
         */
        get: function (contactId, periodId, params) {
          $log.debug('Calendar.getCalendar');

          return workPatternAPI.getCalendar(contactId, periodId, params)
            .then(function (data) {
              return instance.init({
                days: data.values
              }, true);
            });
        }
      });
    }
  ]);
});
