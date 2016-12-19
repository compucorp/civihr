define([
  'common/moment',
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance',
], function (moment, instances) {
  'use strict';

  instances.factory('CalendarInstance', [
    'ModelInstance',
    function (ModelInstance) {

      /**
       * This method checks whether a date matches the send type.
       *
       * @param {string} date
       * @param {string} Type of day
       *
       * @return {Boolean}
       * @throws error if date is not found in calendarData
       */
      function checkDate(date, dayType) {
        var searchedDate = this.days.find(function (data) {
          return moment(data.date).isSame(date);
        });

        if (!searchedDate) {
          throw new Error('Date not found');
        }

        return searchedDate.type.name === dayType;
      }

      return ModelInstance.extend({

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
