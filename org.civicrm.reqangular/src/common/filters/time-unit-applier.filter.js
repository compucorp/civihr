/* eslint-env amd */

define(['common/modules/filters'], function (module) {
  'use strict';

  module.filter('timeUnitApplier', function () {
    var transformers = {
      /**
       * Transforms number into Days format
       *
       * @param  {Number} number
       * @return {String}
       */
      days: function (number) {
        return number + 'd';
      },
      /**
       * Transforms number into Hours format
       *
       * @param  {Number} number
       * @return {String}
       */
      hours: function (number) {
        var hours, minutes, sign;
        var roundMinutes = 0.25;

        if (number === 0 ) {
          return '0h';
        }

        sign = number < 0 ? '-' : '';
        number = Math.abs(number);
        hours = (number >= 1 - roundMinutes ?
          Math.floor(Math.ceil(number / roundMinutes) * roundMinutes) + 'h' : '');
        minutes = (number % 1 && number % 1 <= 1 - roundMinutes && number % 1 >= 0
          ? Math.ceil(number % 1 / roundMinutes) * roundMinutes * 60 + 'm' : '');

        return sign + hours + (hours && minutes ? ' ' : '') + minutes;
      }
    };
    /**
     * Transforms numeric values with a given calculation unit
     * into an expected format for UI
     *
     * @param  {Number|String} value - any number or numeric value
     * @param  {String} unit - calculation unit name (days|hours)
     * @return {String} - formatted string for UI
     */
    return function (value, unit) {
      var number = value === undefined ? 0 : parseFloat(value);

      if (isNaN(number)) {
        throw (new Error('Value must be a number or a numeric string: ' + value));
      }

      return transformers[unit](number);
    };
  });
});
