(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(_dereq_,module,exports){
var Module = angular.module('angular-date', []);

Module.service('DateValidationService', _dereq_('./src/services/DateValidationService'));
Module.filter('CustomDate', _dereq_('./src/filters/CustomDateFilter'));
Module.directive('customDateInput', _dereq_('./src/directives/CustomDateInput'));


},{"./src/directives/CustomDateInput":2,"./src/filters/CustomDateFilter":3,"./src/services/DateValidationService":4}],2:[function(_dereq_,module,exports){
module.exports = function CustomDateInput($filter) {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelController) {

            function convert(data){
                var output = $filter('CustomDate')(data);

                output = (output == 'Unspecified')? '' : output;

                return output;
            }

            ngModelController.$formatters.push(convert);
        }
    };
};

},{}],3:[function(_dereq_,module,exports){
module.exports = function ($filter) {
    return function (datetime) {
        if(typeof datetime === 'string') {
            var match, match2, date;

            match = datetime.match(/^(\d{2})-(\d{2})-(\d{4})/);
            match2 = datetime.match(/^(\d{4})-(\d{2})-(\d{2})/);

            if(match){
                date = new Date(match[3], match[2] - 1, match[1]).getTime();
            } else if(match2){
                date = new Date(match2[1], match2[2] - 1, match2[3]).getTime();
            } else {
                date = datetime;
            }

            if(date < 0 || datetime.length < 10){
                return 'Unspecified';
            } else {
                return $filter('date')(date, 'dd/MM/yyyy');
            }

        } else if(typeof datetime === 'object' && datetime !== null ){
            if(datetime.getTime){
                return $filter('date')(datetime.getTime(), 'dd/MM/yyyy');
            }
        } else if(typeof datetime === 'number'){
            return $filter('date')(datetime, 'dd/MM/yyyy');
        }

        return null;
    };
};

},{}],4:[function(_dereq_,module,exports){
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
            me._error('Please enter valid Start Date!', ['start_date']);
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
            me._error('Neither Days nor Months can be negative or equal to 0.', field_name);
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

module.exports = DateValidationService;
},{}]},{},[1]);
