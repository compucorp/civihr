/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/services',
  'common/services/api',
  'common/models/settings.model'
], function (_, services) {
  'use strict';

  services.factory('FormatCurrencyService', FormatCurrencyService);

  FormatCurrencyService.$inject = ['$log', '$q', 'Settings'];

  function FormatCurrencyService ($log, $q, Settings) {
    $log.debug('Service: FormatCurrencyService');

    return {
      format: format
    };

    /**
    * Adds defined thousand and decimal separators in the given amount
    *
    * @param {String} amount
    * @return {Promise}
    */
    function format (amount) {
      return Settings.fetchSeparators().then(function (separators) {
        return {
          formatted: addSeparators(amount, separators),
          parsed: removeSeparators(amount, separators)
        };
      });
    }

    /**
     * Removes characters other than decimal separator.
     * Converts the formatted amount to numeric value (making calulable)
     * by removing the thousand separators and replacing the decimal separator by " ."
     * and keeping 2 values after decimal separator.
     *
     * @param  {String|Number} amount
     * @param  {Object} separators
     * @return {Number}
     */
    function removeSeparators (amount, separators) {
      var expression = prepareRegexForSeparator(separators.decimal);
      var strippedAmount = amount.toString().replace(new RegExp(expression, 'g'), '');
      var amountWithDecimal = strippedAmount.replace(separators.decimal, '.');

      return parseFloat(amountWithDecimal).toFixed(2);
    }

    /**
     * Prepare regex expression for special characters as following:
     * for characters '\\' and ']'
     *
     * @param  {String} decimalSeparator
     * @return {String} regex expression
     */
    function prepareRegexForSeparator (decimalSeparator) {
      var escape = ['\\', ']'];

      _.includes(escape, decimalSeparator) && (decimalSeparator = '\\' + decimalSeparator);

      return '[^0-9' + decimalSeparator + ']';
    }

    /**
     * Formats the given amount with thousand separators
     *
     * @param  {String|Number} amount
     * @param  {Object} separators
     * @return {String}
     */
    function addSeparators (amount, separators) {
      return addThousandSeparator(replaceDecimalSeparator(amount, separators.decimal), separators);
    }

    /**
     * Replaces the decimal separator "." by defined decimal separator
     *
     * Add given amount is floating point value with decimal separator as "." ,
     * we need to change "." into defined decimal separator for proper formatting
     *
     * @param {String|Number} amount floating point number  (eg. 123213.98 or 123213.98)
     * @param {String} decimalSeparator defined decimal separator
     * @returns {String}
     */
    function replaceDecimalSeparator (amount, decimalSeparator) {
      amount = amount.toString();

      if (+amount && amount.indexOf('.') !== -1) {
        amount = amount.replace('.', decimalSeparator);
      }

      return amount;
    }

    /**
     * Add thousand separators to the given amount.
     *
     * @param {String} amount
     * @return {String}
     */
    function addThousandSeparator (amount, separators) {
      var amountAfterDecimal;
      var amountBeforeDecimal;
      var decimalIndex;
      var expression;

      expression = prepareRegexForSeparator(separators.decimal);
      // Remove all characters except decimal separator
      amount = amount.toString().replace(new RegExp(expression, 'g'), '');

      // Find the index of first ocuring decimal separator
      decimalIndex = amount.indexOf(separators.decimal);

      if (decimalIndex !== -1) {
        // Hold the whole number before decimal separator
        amountBeforeDecimal = +(amount.substring(0, decimalIndex));
        // Remove all non numeric characters form values after decimal separator
        amountAfterDecimal = amount.substring(decimalIndex + 1).replace(/[^0-9]/g, '');
      } else {
        amountBeforeDecimal = +(amount.replace(/[^0-9]/g, ''));
      }

      // Add thousand separators to the value before decimal separator
      // converting it to string
      amountBeforeDecimal = amountBeforeDecimal.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + separators.thousand);

      // if amountAfterDecimal exists, concat amountBeforeDecimal and amountAfterDecimal by separator
      // keeping only two values after decimal separator
      return (decimalIndex !== -1) ? (amountBeforeDecimal.toString() + separators.decimal + amountAfterDecimal.substring(0, 2)) : amountBeforeDecimal;
    }
  }
});
