/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models',
  'common/models/model',
  'leave-absences/shared/apis/work-pattern-api',
  'leave-absences/shared/instances/calendar.instance'
], function (_, models) {
  'use strict';

  models.factory('Calendar', ['$log', 'Model', 'WorkPatternAPI', 'CalendarInstance',
    function ($log, Model, workPatternAPI, instance) {
      $log.debug('Calendar');

      return Model.extend({

        /**
         * This method returns the calendar(s) for the given contact(s) and period,
         * as a list of days and their type
         *
         * @param {string/int/Array} contactId can be also an array for multiple contacts
         * @param {string} periodId
         * @param {object} params additional parameters
         * @return {Promise} resolves with CalendarInstance(s)
         */
        get: function (contactId, periodId, params) {
          $log.debug('Calendar.get');

          return workPatternAPI.getCalendar(contactId, periodId, params)
            .then(function (data) {
              var list = data.values.map(function (contactCalendar) {
                return instance.init(contactCalendar);
              });

              return _.isArray(contactId) ? list : list[0];
            });
        }
      });
    }
  ]);
});
