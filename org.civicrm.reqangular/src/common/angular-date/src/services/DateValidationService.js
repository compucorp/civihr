/**
 *
 * @param $filter
 * @param DateFactory
 * @constructor
 */
function DateValidationService($filter, DateFactory) {
    var me = this;
    
    me.maxDate = {};
    me.minDate = {};

    me.options = {};

    me.setMinDate = function(date){
        me.minDate = DateFactory.createDate(date);
    };

    me.setMaxDate = function(date){
        me.maxDate = DateFactory.createDate(date);
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
        if (typeof error === 'function') {
            me._error = error;
        } else {
            throw new TypeError('Error callback must be a function.');
        }
    };

    me.validate = function validate(start, end) {

        if(start instanceof Date){
            start = start.getTime();
        }

        if(end instanceof Date){
            end = end.getTime();
        }

        var start_date = DateFactory.createDate(start);
        var end_date = DateFactory.createDate(end);

        checkIfValuesAreValid(start_date, ['start_date']);
        checkIfValuesAreInRange(start_date, ['start_date']);

        if(end !== '' && typeof end !== 'undefined' && end !== null){
            checkIfValuesAreValid(end_date, ['end_date']);
            checkIfValuesAreInRange(end_date, ['end_date']);
        }

        if(start && end_date.isValid()){
            checkIfStartDateIsLower(start_date, end_date);
        }
    };

    function checkIfValuesAreValid(date, fields){
        if(!date.isValid()){
            me._error('Date is not valid!!', fields);
        }
    }

     function checkIfValuesAreInRange(date, field_name) {
        if (me.minDate.isAfter) {
            if (me.minDate.isAfter(date)) {
                me._error('Date cannot be lower than ' + me.minDate.format('DD/MM/YYYY') + '.', field_name);
            }
        }
        if (me.maxDate.isBefore) {
            if (me.maxDate.isBefore(date)) {
                me._error('Date cannot be higher than ' + me.maxDate.format('DD/MM/YYYY') + '.', field_name);
            }
        }
    }

    function checkIfStartDateIsLower(start, end) {
        if(start.isAfter(end)){
            me._error('Start Date cannot be higher than End Date.', ['start_date', 'end_date']);
        }

        if(start.isSame(end)){
            me._error('Start Date and End Date cannot be the same.', ['start_date', 'end_date']);
        }
    }
}

module.exports = DateValidationService;
