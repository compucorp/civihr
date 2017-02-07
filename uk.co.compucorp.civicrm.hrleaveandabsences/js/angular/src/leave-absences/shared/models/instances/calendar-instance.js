define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance',
], function (_, moment, instances) {
  'use strict';

  instances.factory('CalendarInstance', [
    '$log', 'ModelInstance',
    function ($log, ModelInstance) {

      var serverDateFormat = 'YYYY-MM-DD';

      /**
       * This method checks whether a date matches the send type.
       *
       * @param {Object} date
       * @param {string} Type of day
       *
       * @return {Boolean}
       * @throws error if date is not found in calendarData
       */
      function checkDate(date, dayType) {
        var searchedDate = this.days[getDateObjectWithFormat(date).valueOf()];

        if (!searchedDate) {
          throw new Error('Date not found');
        }

        return searchedDate.type.name === dayType;
      }

      /**
       * Converts given date to moment object with server format
       *
       * @param {Date/String} date from server
       * @return {Date} Moment date
       */
      function getDateObjectWithFormat(date) {
        return moment(date, serverDateFormat).clone();
      }

      return ModelInstance.extend({

        /**
         * Creates a new instance, optionally with its data normalized.
         * Also, it will allow children to add/remove/update current attributes of
         * the instance using transformAttributes method
         *
         * @param {object} data - The instance data
         * @return {object}
         */
        init: function (data) {
          var datesObj = {};

          // convert array to an object with the timestamp being the key
          data.forEach(function (calendar) {
            datesObj[getDateObjectWithFormat(calendar.date).valueOf()] = calendar;
          });

          return _.assign(Object.create(this), {
            days: datesObj
          });
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
