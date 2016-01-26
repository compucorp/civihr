var moment = require('../../../vendor/moment.min.js');

function DateFactory() {
    return {
        moment: moment,
        /**
         * Wrapper for moment()
         * @param dateString
         * @param format
         * @param strict
         * @returns Moment Object
         */
        createDate: function createDate(dateString, format, strict) {

            if (!format) {
                format = [
                    'DD/MM/YYYY',
                    'DD-MM-YYYY',
                    'x'
                ];
            }

            if(typeof strict === 'undefined'){
                strict = true;
            }

            return moment(dateString, format, strict);
        }
    };
}

module.exports = DateFactory;
