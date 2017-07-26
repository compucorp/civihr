/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/models-instances',
  'common/models/option-group',
  'common/models/instances/instance'
], function (_, moment, instances) {
  'use strict';

  instances.factory('CalendarInstance', [
    '$log', '$q', 'ModelInstance', 'shared-settings', 'OptionGroup',
    function ($log, $q, ModelInstance, sharedSettings, OptionGroup) {
      var dayTypesPromise;

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
         * Checks whether the given date is a non working day
         *
         * @param {Object} date
         * @return {Promise} resolves to {Boolean}
         */
        isNonWorkingDay: function (date) {
          return checkDateType.call(this, date, 'non_working_day');
        },

        /**
         * Checks whether a date is a weekend
         *
         * @param {Object} date
         * @return {Promise} resolves to {Boolean}
         */
        isWeekend: function (date) {
          return checkDateType.call(this, date, 'weekend');
        },

        /**
         * Checks whether the given date is a working day
         *
         * @param {Object} date
         * @return {Promise} resolves to {Boolean}
         */
        isWorkingDay: function (date) {
          return checkDateType.call(this, date, 'working_day');
        },

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

          return _(attributes).omit('calendar').assign({ days: datesObj }).value();
        }
      });

      /**
       * Checks whether a date matches the given type name
       *
       * @param {Object} date
       * @param {string} typeName
       *
       * @return Promise resolves to {Boolean}
       */
      function checkDateType (date, typeName) {
        return loadDayTypes()
          .then(function (dayTypes) {
            var searchedDate = this.days[getDateObjectWithFormat(date).valueOf()];

            return searchedDate ? _.find(dayTypes, function (dayType) {
              return dayType.name === typeName;
            }).value === searchedDate.type : false;
          }.bind(this));
      }

      /**
       * Converts given date to moment object with server format
       *
       * @param {Date/String} date from server
       * @return {Object} Moment date
       */
      function getDateObjectWithFormat (date) {
        return moment(date, sharedSettings.serverDateFormat).clone();
      }

      /**
       * Fetches the list of day types OptionValues and stores the promise
       * internally so that future calls will not make any more requests
       *
       * @return {Promise} resolves to {Array}
       */
      function loadDayTypes () {
        dayTypesPromise = dayTypesPromise || OptionGroup.valuesOf('hrleaveandabsences_work_day_type');

        return dayTypesPromise;
      }
    }]);
});
