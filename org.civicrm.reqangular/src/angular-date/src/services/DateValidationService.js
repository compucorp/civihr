function DateValidationService($filter) {
    var me = this;

    me.start = '';
    me.end = '';

    me.start_parts = [];
    me.end_parts = [];

    me.options = {};

    me.setOptions = function setOptions(newOptions) {
        angular.extend(me.options, newOptions);
    };

    me._error = function (error_msg, fields) {
        throw new Error(error_msg);
    };

    /**
     *
     * @param {function} error
     * @throws TypeError
     */
    me.setErrorCallback = function setErrorCallback(error) {
        if (error) {
            me._error = error;
        } else {
            throw new TypeError('Error callback is not defined.');
        }
    };

    me.validate = function validate(start_date, end_date) {
        // We apply filter, so we have the same date format in both variables
        me.start = $filter('customDate')(start_date);
        me.end = $filter('customDate')(end_date);

        me.start_parts = me.start.split('/');
        me.end_parts = me.end.split('/');

        me.checkIfStartDateIsLower(s, e, 2);
        //Check for year, month and day
        for (var index = 2; index > -1; index--) {
            me.checkIfValuesAreValid(index, e, ['end_date']);
            me.checkIfValuesAreValid(index, s, ['start_date']);
        }
    };

    me.checkIfFormatIsValid = function () {
        if (me.start.length !== 10 || me.start_parts.length != 3 || me.checkFormat(me.start_parts)) {
            me._error('Date is invalid! Valid date format is: dd/mm/yyyy.', ['start_date']);
        }
        if (me.end.length !== 10 || me.end_parts.length != 3 || me.checkFormat(me.end_parts)) {
            me._error('Date is invalid! Valid date format is: dd/mm/yyyy.', ['end_date']);
        }
    };

    /**
     * @static
     * @param {array} date_parts
     * @returns {boolean}
     */
    me.checkFormat = function checkFormat(date_parts) {
        return !(date_parts[0].length == 2 && date_parts[1].length == 2 && date_parts[2].length == 4);
    };

    me.checkIfValuesAreValid = function checkIfValuesAreValid(index, date, field_name) {
        if (index == 2 && (parseInt(date[index], 10) < $scope.minDate.getFullYear())) {
            me._error('Year cannot be lower than ' + $scope.minDate.getFullYear() + '.', field_name);
        }

        if (index == 2 && (parseInt(date[index], 10) > $scope.maxDate.getFullYear())) {
            me._error('Year cannot be higher than ' + $scope.maxDate.getFullYear() + '.', field_name);
        }

        if (parseInt(date[index], 10) < 1) {
            me._error('Neither Days or Months can be negative or equal to 0.', field_name);
        }

        if (index == 1 && (parseInt(date[index], 10) > 12)) {
            me._error('This month doesn\'t exist.', field_name);
        }

        if (index === 0 && (parseInt(date[index], 10) > 31)) {
            me._error('Day of the month is invalid.', field_name);
        }
    };

    /**
     * Recursive function checking if start date is lower than end date
     * @static
     * @param {array} start_parts
     * @param {array} end_parts
     * @param {int} index
     * @returns {boolean}
     */
    me.checkIfStartDateIsLower = function checkIfStartDateIsLower(start_parts, end_parts, index) {
        //Prevent endless iteration
        if (index < 0) return true;

        if (parseInt(end_parts[index], 10) < parseInt(start_parts[index], 10)) {
            me._error('Start Date cannot be higher than End Date!', ['start_date', 'end_date']);
        } else if (parseInt(end_parts[index], 10) == parseInt(start_parts[index], 10)) {
            return me.checkIfStartDateIsLower(start_parts, end_parts, index - 1);
        }
    };
}

module.exports = DateValidationService;