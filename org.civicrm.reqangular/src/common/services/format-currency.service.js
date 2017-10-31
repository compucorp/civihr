/* eslint-env amd */

define([
  'common/modules/services',
  'common/services/api',
  'common/models/settings.model'
], function (services) {
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
    * @param {string} amount
    * @return {promise}
    */
    function format (amount) {
      return Settings.fetchSeparators().then(function (separators) {
        return {
          formatted: addSeparators(amount, separators),
          unformatted: removeSeparators(amount, separators)
        };
      });
    }

    /**
     * Removes characters other than decimal separator
     * Converts the formatted amount to numeric value (making calulable) by
     * removing the thousand separators and replacing the decimal separator by " ."
     *
     * @param  {string|Integer} amount
     * @param  {object} separators
     * @return {integer}
     */
    function removeSeparators (amount, separators) {
      var expression = configureExpression(separators.decimal);
      var strippedAmount = amount.toString().replace(new RegExp(expression, 'g'), '');
      var amountWithDecimal = strippedAmount.replace(separators.decimal, '.');

      return amountWithDecimal;
    }

    /**
     * Configure expression for special characters like \ and ]
     *
     * @param  {string} decimalSeparator
     * @return {string}
     */
    function configureExpression (decimalSeparator) {
      var expression;

      // Handle reserved regex characters that might be used as separators
      switch (decimalSeparator) {
        case '\\':
          expression = '[^0-9\\\\]';
          break;
        case ']':
          expression = '[^0-9\\]]';
          break;
        default:
          expression = '[^0-9' + decimalSeparator + ']';
      }

      return expression;
    }

    /**
     * Formats the given amount with thousand separators
     *
     * @param  {string|integer} amount
     * @param  {object} separators
     * @return {string}
     */
    function addSeparators (amount, separators) {
      var amountAfterDecimal; // Keep 2 decimal values after decimal
      var amountBeforeDecimal;
      var decimalIndex;
      var expression;

      // Handling special case:
      // If we are given to format the amount with decimal as "." ,
      // we need to change "." into defined decimal separator for proper formatting
      if (+amount && (amount.toString()).indexOf('.') !== -1) {
        amount = (amount.toString()).replace('.', separators.decimal);
      }

      // Regex expression to remove all non numeric values
      // except defined decimal separator in the string
      expression = configureExpression(separators.decimal);

      // Remove all characters except decimal characters
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
      return (decimalIndex !== -1) ? (amountBeforeDecimal.toString() + separators.decimal + amountAfterDecimal) : amountBeforeDecimal;
    }
  }
});
