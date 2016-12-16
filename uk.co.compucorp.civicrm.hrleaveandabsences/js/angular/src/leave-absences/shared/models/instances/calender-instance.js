define([
  'common/moment',
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance',
], function (moment, instances) {
  'use strict';

  instances.factory('CalenderInstance', [
    'ModelInstance',
    function (ModelInstance) {

      /**
       * This method checks whether a date matches the send type.
       *
       * @param {string} Date
       * @param {string} Type of day
       *
       * @return {Boolean}
       * Throws Error if date is not found in calenderData
       */
      function checkDate(date, dayType) {
        var searchedDate = this.calenderData.find(function (data) {
          return moment(data.date).isSame(moment(date));
        });

        if (!searchedDate) {
          throw new Error("Date not found");
        }

        return searchedDate.type.name === dayType;
      }

      return ModelInstance.extend({

        /**
         * This object contains the calendar data.
         * Default value is empty Array
         */
        calenderData: [],

        /**
         * This method checks whether a date is working day.
         *
         * @param {string} Date
         * @return {Boolean}
         */
        isWorkingDay: function (date) {
          return checkDate.call(this, date, "working_day");
        },

        /**
         * This method checks whether a date is non working day.
         *
         * @param {string} Date
         * @return {Boolean}
         */
        isNonWorkingDay: function (date) {
          return checkDate.call(this, date, "non_working_day");
        },

        /**
         * This method checks whether a date is weekend.
         *
         * @param {string} Date
         * @return {Boolean}
         */
        isWeekend: function (date) {
          return checkDate.call(this, date, "weekend");
        }
      });
    }]);
});
