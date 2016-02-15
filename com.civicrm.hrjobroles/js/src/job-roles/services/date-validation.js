define([
    'job-roles/services/services',
    'common/moment'
], function (module, moment) {
    /**
     * Service responsible for validating dates in HRJobRoles
     * @constructor
     */
    module.service('DateValidation', ['HR_settings', function (HR_settings) {
        var me = this;

        me._error = function (error_msg, fields) {
            throw new Error(error_msg, fields[0]);
        };

        /**
         *
         * @param {function} error
         * @throws TypeError
         */
        me.setErrorCallback = function setErrorCallback(error) {
            if (typeof error === 'function') {
                me._error = error;
            } else {
                throw new TypeError('Error callback must be a function.');
            }
        };

        me.dateFormats = [
            HR_settings.DATE_FORMAT || 'DD/MM/YYYY',
            'x',
            'YYYY-MM-DD'
        ];

        me.validate = function validate(start, end) {

            if (start instanceof Date) {
                start = start.getTime();
            }

            if (end instanceof Date) {
                end = end.getTime();
            }

            var start_date = moment(start, me.dateFormats, true);
            var end_date = moment(end, me.dateFormats, true);

            checkIfValuesAreValid(start_date, ['start_date']);

            if (end !== '' && typeof end !== 'undefined' && end !== null) {
                checkIfValuesAreValid(end_date, ['end_date']);
            }

            if (start && end_date.isValid()) {
                checkIfStartDateIsLower(start_date, end_date);
            }
        };

        function checkIfValuesAreValid(date, fields) {
            if (!date.isValid()) {
                me._error('Date is not valid!!', fields);
            }
        }

        function checkIfStartDateIsLower(start, end) {
            if (start.isAfter(end)) {
                me._error('Start Date cannot be higher than End Date.', ['start_date', 'end_date']);
            }

            if (start.isSame(end)) {
                me._error('Start Date and End Date cannot be the same.', ['start_date', 'end_date']);
            }
        }
    }]);
});
