define([
    'job-roles/services/services',
    'common/moment'
], function (module, moment) {
    /**
     * Service responsible for validating dates in HRJobRoles
     * @constructor
     */
    module.factory('DateValidation', ['HR_settings', function (HR_settings) {
        /**
         *
         * @param error_msg
         * @param fields
         * @private
         */
        var _error = function (error_msg, fields) {
            throw new Error(error_msg, fields[0]);
        };

        /**
         * Method checking whether provided date is valid
         * @param {moment} date
         * @param {string[]} fields
         */
        function checkIfValuesAreValid(date, fields) {
            if (!date.isValid()) {
                _error('Date is not valid!!', fields);
            }
        };

        /**
         * Method checking whether dates are in valid order
         * @param {moment} start
         * @param {moment} end
         */
        function checkIfStartDateIsLower (start, end) {
            if (start.isSameOrAfter(end)) {
                _error('Start Date cannot be the same as or after the End Date.', ['start_date', 'end_date']);
            }
        }

        function checkIfContractStartDateIsLowerThanStart (contract_start, start_date) {
            if (contract_start.isAfter(start_date)) {
                _error('Start Date cannot be lower than Contract Start Date.', ['start_date']);
            }
        }

        function checkIfStartIsLowerThanContractEnd (start_date, contract_end) {
            if (start_date.isAfter(contract_end)) {
                _error('Start Date cannot be higher than Contract End Date.', ['start_date']);
            }
        }

        function checkIfEndIsEqualOrLowerThanContractEnd (end_date, contract_end) {
            if (end_date.isAfter(contract_end)) {
                _error('End Date cannot be higher than Contract End Date.', ['end_date']);
            }
        }

        function formatDate(date) {
          if (date instanceof Date) {
            date.setHours(0);
            date.setMinutes(0);
            date.setSeconds(0);

            return date.getTime();
          } else {
            return date;
          }
        }

        var Validation = {
            dateFormats: [
                'x',
                'YYYY-MM-DD'
            ],

            /**
             * Set custom error callback
             * @param {function} error
             */
            setErrorCallback: function setErrorCallback(error) {
                if (typeof error === 'function') {
                    _error = error;
                } else {
                    throw new TypeError('Error callback must be a function.');
                }
            },

            /**
             * Validates Dates
             * @param {Date|string|int} start
             * @param {Date|string|int} end
             */
            validate: function validate(start, end, contract_start, contract_end) {
                start = formatDate(start);
                end = formatDate(end);
                contract_start = formatDate(contract_start);
                contract_end = formatDate(contract_end);

                var start_date = moment(start, this.dateFormats, true);
                var end_date = moment(end, this.dateFormats, true);

                contract_start = moment(contract_start, this.dateFormats, true);
                contract_end = moment(contract_end, this.dateFormats, true);

                checkIfValuesAreValid(start_date, ['start_date']);

                if (end === 0 || end) {
                    checkIfValuesAreValid(end_date, ['end_date']);

                    if (start) {
                        checkIfStartDateIsLower(start_date, end_date);
                        checkIfContractStartDateIsLowerThanStart(contract_start, start_date);

                        if (contract_end.isValid()) {
                          checkIfStartIsLowerThanContractEnd(start_date, contract_end);
                        }
                    }

                    if (contract_end.isValid()) {
                      checkIfEndIsEqualOrLowerThanContractEnd(end_date, contract_end);
                    }
                }
            }
        };
        HR_settings.DATE_FORMAT && Validation.dateFormats.push(HR_settings.DATE_FORMAT.toUpperCase());

        return Validation;
    }]);
});
