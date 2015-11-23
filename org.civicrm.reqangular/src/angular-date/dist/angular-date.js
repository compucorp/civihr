var Module = angular.module('angular-date', []);

Module.service('DateValidationService', DateValidationService);
Module.filter('CustomDate', CustomDateFilter);
Module.directive('customDateInput', CustomDateInput);


function CustomDateInput($filter) {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelController) {

            function convert(data){
                return $filter('CustomDate')(data);
            }

            ngModelController.$formatters.push(convert);
        }
    };
}
function CustomDateFilter($filter) {
    return function (datetime) {
        if(typeof datetime === 'string') {
            var match, match2, date;

            match = datetime.match(/^(\d{2})-(\d{2})-(\d{4})/);
            match2 = datetime.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if(match){
                date = new Date(match[3], match[2] - 1, match[1]);
                return $filter('date')(date.getTime(), 'dd/MM/yyyy');
            } else if(match2){
                date = new Date(match2[1], match2[2] - 1, match2[3]);
                return $filter('date')(date.getTime(), 'dd/MM/yyyy');
            }

            return $filter('date')(datetime, 'dd/MM/yyyy');

        } else if(typeof datetime === 'object'){
            if(datetime.getTime){
                return $filter('date')(datetime.getTime(), 'dd/MM/yyyy');
            }
        } else if(typeof datetime === 'number'){
            return $filter('date')(datetime, 'dd/MM/yyyy');
        }

        return null;
    };
}

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

    me._reset = function(){
        me.start = '';
        me.end = '';

        me.start_parts = [];
        me.end_parts = [];
    };

    /**
     *
     * @param {function} error
     * @throws TypeError
     */
    me.setErrorCallback = function setErrorCallback(error) {
        if (!!(error && error.constructor && error.call && error.apply)) {
            me._error = error;
        } else {
            throw new TypeError('Error callback must be a function.');
        }
    };

    me.setDates = function(start_date, end_date){
        me._reset();
        // We apply filter, so we have the same date format in both variables
        if(!!start_date) {
            me.start = $filter('CustomDate')(start_date);
            me.start_parts = me.start.split('/');
        } else {
            me._error('Start date is required!', ['start_date']);
        }

        if(!!end_date) {
            me.end = $filter('CustomDate')(end_date);
            me.end_parts = me.end.split('/');
        }
    };

    me.validate = function validate(start_date, end_date) {

        if(!me.start_date || !!start_date) me.setDates(start_date, end_date);

        me.checkIfFormatIsValid();
        me.checkIfStartDateIsLower(me.start_parts, me.end_parts, 2);
        //Check for year, month and day
        for (var index = 2; index > -1; index--) {
            if(me.end) {
                me.checkIfValuesAreValid(index, me.end_parts, ['end_date']);
            }
            me.checkIfValuesAreValid(index, me.start_parts, ['start_date']);
        }
    };

    me.checkIfFormatIsValid = function () {
        if (me.start.length !== 10 || me.start_parts.length != 3 || me.checkFormat(me.start_parts)) {
            me._error('Date is invalid! Valid date format is: dd/mm/yyyy.', ['start_date']);
        }

        if(me.end) {
            if (me.end.length !== 10 || me.end_parts.length != 3 || me.checkFormat(me.end_parts)) {
                me._error('Date is invalid! Valid date format is: dd/mm/yyyy.', ['end_date']);
            }
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
        if (me.options.minDate) {
            if (index == 2 && (parseInt(date[index], 10) < me.options.minDate.getFullYear())) {
                me._error('Year cannot be lower than ' + me.options.minDate.getFullYear() + '.', field_name);
            }
        }
        if (me.options.maxDate) {
            if (index == 2 && (parseInt(date[index], 10) > me.options.maxDate.getFullYear())) {
                me._error('Year cannot be higher than ' + me.options.maxDate.getFullYear() + '.', field_name);
            }
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
        if(isNaN(parseInt(date[index], 10))){
            me._error('It\'s not a date!', field_name);
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
        if (index < 0){
            me._error('Start Date cannot be he same as End Date!', ['start_date', 'end_date']);
            return true;
        }

        if (parseInt(end_parts[index], 10) < parseInt(start_parts[index], 10)) {
            me._error('Start Date cannot be higher than End Date!', ['start_date', 'end_date']);
        } else if (parseInt(end_parts[index], 10) == parseInt(start_parts[index], 10)) {
            return me.checkIfStartDateIsLower(start_parts, end_parts, index - 1);
        }
    };
}
