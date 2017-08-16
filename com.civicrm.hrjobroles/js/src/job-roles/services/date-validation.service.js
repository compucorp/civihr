/* eslint-env amd */

define([
  'common/moment'
], function (moment) {
  /**
   * Service responsible for validating dates in HRJobRoles
   * @constructor
   */
  dateValidation.$inject = ['HR_settings'];

  function dateValidation (hrSettings) {
    /**
     *
     * @param errorMsg
     * @param fields
     */
    var _error = function (errorMsg, fields) {
      throw new Error(errorMsg, fields[0]);
    };

    var Validation = {
      dateFormats: ['x', 'YYYY-MM-DD'],

      /**
       * Set custom error callback
       *
       * @param {function} error
       */
      setErrorCallback: function (error) {
        if (typeof error === 'function') {
          _error = error;
        } else {
          throw new TypeError('Error callback must be a function.');
        }
      },

      /**
       * Validates Dates
       *
       * @param {Date|string|int} start
       * @param {Date|string|int} end
       * @param {Date|string|int} contractStart
       * @param {Date|string|int} contractEnd
       */
      validate: function (start, end, contractStart, contractEnd) {
        start = formatDate(start, this.dateFormats);

        contractStart = formatDate(contractStart, this.dateFormats);
        contractEnd = formatDate(contractEnd, this.dateFormats);

        checkIfValuesAreValid(start, ['start_date']);
        checkIfStartIsLowerThanContractEnd(start, contractEnd);
        checkIfStartIsLowerThanContractStart(start, contractStart);

        if (end === 0 || end) {
          end = formatDate(end, this.dateFormats);

          checkIfValuesAreValid(end, ['end_date']);
          checkIfEndIsEqualOrLowerThanContractEnd(end, contractEnd);

          checkIfStartDateIsLower(start, end);
        }
      }
    };

    hrSettings.DATE_FORMAT && Validation.dateFormats.push(hrSettings.DATE_FORMAT.toUpperCase());

    return Validation;

    /**
     * Method checking whether provided date is valid
     *
     * @param {moment} date
     * @param {string[]} fields
     */
    function checkIfValuesAreValid (date, fields) {
      if (!date.isValid()) {
        _error('Date is not valid!!', fields);
      }
    }

    /**
     * Method checking whether dates are in valid order
     *
     * @param {moment} start
     * @param {moment} end
     */
    function checkIfStartDateIsLower (start, end) {
      if (start.isSameOrAfter(end)) {
        _error('Start Date cannot be the same as or after the End Date.', ['start_date', 'end_date']);
      }
    }

    /**
     * Check if job role start date is lower than contract start date
     *
     * @param {moment} start
     * @param {moment} contractStart
     */
    function checkIfStartIsLowerThanContractStart (start, contractStart) {
      if (start.isBefore(contractStart)) {
        _error('Start Date cannot be lower than Contract Start Date.', ['start_date']);
      }
    }

    /**
     * Check if job role start date is higher than contract end date
     *
     * @param {moment} start
     * @param {moment} contractEnd
     */
    function checkIfStartIsLowerThanContractEnd (start, contractEnd) {
      if (start.isAfter(contractEnd)) {
        _error('Start Date cannot be higher than Contract End Date.', ['start_date']);
      }
    }

    /**
     * Check if job role end date is lower than contract end date
     *
     * @param {moment} end
     * @param {moment} contractEnd
     */
    function checkIfEndIsEqualOrLowerThanContractEnd (end, contractEnd) {
      if (end.isAfter(contractEnd)) {
        _error('End Date cannot be higher than Contract End Date.', ['end_date']);
      }
    }

    /**
     * Format date using moment
     *
     * @param {Date|string|int} start
     * @param {array} dateFormats
     * @returns {moment}
     */
    function formatDate (date, dateFormats) {
      if (date instanceof Date) {
        date = moment(date).valueOf();
      }

      return moment(date, dateFormats, true).startOf('day');
    }
  }

  return dateValidation;
});
