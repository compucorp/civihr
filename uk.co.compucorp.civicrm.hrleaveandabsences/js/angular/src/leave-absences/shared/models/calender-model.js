define([
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/calender-instance',
  'leave-absences/shared/apis/work-pattern-api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Calender', [
    '$log',
    'Model',
    'WorkPatternAPI',
    'CalenderInstance',
    function ($log, Model, workPatternAPI, instance) {
      $log.debug('Calender');
      return Model.extend({

        /**
         * This method returns the calendar for a specific period, as a list of days and their type
         *
         * @param {string} contactId The ID of the Contact
         * @param {string} periodId The ID of the Absence Period
         * @param {object} params
         * @return {Promise} Resolved with {Object} Calender Instance or Error Data
         */
        getCalendar: function (contactId, periodId, params) {
          $log.debug('Calender.getCalendar');
          return workPatternAPI.getCalendar(contactId, periodId, params)
            .then(function (data) {
              if (data.is_error === 1) {
                return data;
              }
              return instance.init({
                calenderData: data.values
              }, true);
            });
        }
      });
    }
  ]);
});
