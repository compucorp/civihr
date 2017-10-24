/* eslint-env amd */

define([
  'common/modules/services',
  'common/services/api'
], function (services) {
  'use strict';

  services.factory('FormatCurrencyService', FormatCurrencyService);

  FormatCurrencyService.$inject = ['api', '$q', '$log'];

  function FormatCurrencyService (api, $q, $log) {
    $log.debug('Service: FormatCurrencyService');

    var promise;

    return {
      addSeperators: addSeperators,
      removeCharacters: removeCharacters
    };

    /**
     * Adds defined thousand and decimal separators in the given amount
     *
     * @param {string} amount
     * @return {string}
     */
    function addSeperators (amount) {
      return fetchSeparators().then(function (separators) {
        // Add thousand seperator to amount using regex expression
        var withThousandSeparator = amount.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + separators.thousand);
        var formattedCurrency = withThousandSeparator.replace('.', separators.decimal);

        return (+amount) ? formattedCurrency : ('0' + separators.decimal + '00');
      });
    }

    /**
     * Fetched the thousand and decimal seperators form backend
     *
     * @return {promise}
     */
    function fetchSeparators () {
      promise = promise || getSettings();

      return promise.then(function (result) {
        return {
          decimal: result.monetaryDecimalPoint,
          thousand: result.monetaryThousandSeparator
        };
      });
    }

    /**
     * Gets defined settings data form backend
     *
     * @param  {Object} params
     *
     * @return {Promise}
     */
    function getSettings () {
      return api.sendGET('Setting', 'get').then(function (data) {
        return data.values[0];
      });
    }

    /**
     * Removes characters other than decimal separator
     * Converts the formatted amount to numeric value (making calulable) by
     * removing the thousand separators and replacing the decimal separator by " ."
     *
     * @param  {string|Integer} amount
     * @return {integer}
     */
    function removeCharacters (amount) {
      return fetchSeparators().then(function (separators) {
        var expression = '[^0-9' + separators.decimal + ']';
        var strippedAmount = amount.toString().replace(new RegExp(expression, 'g'), '');
        var amountWithDecimal = strippedAmount.replace(separators.decimal, '.');

        return amountWithDecimal;
      });
    }
  }
});
