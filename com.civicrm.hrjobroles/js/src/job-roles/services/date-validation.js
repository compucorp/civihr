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
        var checkIfValuesAreValid = function (date, fields) {
            if (!date.isValid()) {
                _error('Date is not valid!!', fields);
            }
        };

        /**
         * Method checking whether dates are in valid order
         * @param {moment} start
         * @param {moment} end
         */
        var checkIfStartDateIsLower = function (start, end) {
            if (start.isSameOrAfter(end)) {
                _error('Start Date cannot be the same as or after the End Date.', ['start_date', 'end_date']);
            }
        };

        return {
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
            validate: function validate(start, end) {
                HR_settings.DATE_FORMAT && this.dateFormats.push(HR_settings.DATE_FORMAT.toUpperCase());

                if (start instanceof Date) {
                    start = start.getTime();
                }

                if (end instanceof Date) {
                    end = end.getTime();
                }

                var start_date = moment(start, this.dateFormats, true);
                var end_date = moment(end, this.dateFormats, true);

                checkIfValuesAreValid(start_date, ['start_date']);

                if (end === 0 || end) {
                    checkIfValuesAreValid(end_date, ['end_date']);

                    if (start) {
                        checkIfStartDateIsLower(start_date, end_date);
                    }
                }
            }
        };
    }]);
});
