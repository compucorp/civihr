/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (_, moment, instances) {
  'use strict';

  instances.factory('CalendarInstance', [
    '$log', 'ModelInstance', 'shared-settings',
    function ($log, ModelInstance, sharedSettings) {
      /**
       * This method checks whether a date matches the send type.
       *
       * @param {Object} date
       * @param {string} Type of day
       *
       * @return {Boolean}
       * @throws error if date is not found in calendarData
       */
      function checkDate (date, dayType) {
        var searchedDate = this.days[getDateObjectWithFormat(date).valueOf()];

        return searchedDate ? searchedDate.type.name === dayType : false;
      }

      /**
       * Converts given date to moment object with server format
       *
       * @param {Date/String} date from server
       * @return {Date} Moment date
       */
      function getDateObjectWithFormat (date) {
        return moment(date, sharedSettings.serverDateFormat).clone();
      }

      return ModelInstance.extend({

        /**
         * Removes the `calendar` property and creates the `day` property
         * which indexes the dates by their timestamp
         *
         * @param  {Object} attributes
         * @return {Object}
         */
        transformAttributes: function (attributes) {
          var datesObj = {};

          // convert array to an object with the timestamp being the key
          attributes.calendar.forEach(function (calendar) {
            datesObj[getDateObjectWithFormat(calendar.date).valueOf()] = calendar;
          });

          return _(attributes)
            .omit('calendar')
            .assign({ days: datesObj })
            .value();
        },

        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            days: []
          };
        },

        /**
         * This method checks whether a date is working day.
         *
         * @param {Object} date
         * @return {Boolean}
         */
        isWorkingDay: function (date) {
          return checkDate.call(this, date, 'working_day');
        },

        /**
         * This method checks whether a date is non working day.
         *
         * @param {Object} date
         * @return {Boolean}
         */
        isNonWorkingDay: function (date) {
          return checkDate.call(this, date, 'non_working_day');
        },

        /**
         * This method checks whether a date is weekend.
         *
         * @param {Object} date
         * @return {Boolean}
         */
        isWeekend: function (date) {
          return checkDate.call(this, date, 'weekend');
        }
      });
    }]);
});
