var moment = require('../../../vendor/moment.min.js');

function DateFactory(){
    return {
        moment: moment,
        /**
         * Wrapper for moment.utc()
         * @param dateString
         * @param format
         * @param strict
         * @returns Moment Object
         */
        createDate: function createDate(dateString, format, strict){

            return this.moment.apply(null, arguments);
        }
    };
}

module.exports = DateFactory;