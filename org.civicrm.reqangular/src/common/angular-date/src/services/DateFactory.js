var moment = require('../../../vendor/moment.min.js');

function DateFactory($q) {
    return {
        /**
         * Default Format
         */
        dateFormat: 'DD-MM-YYYY',

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

            if (typeof strict === 'undefined') {
                strict = true;
            }

            return moment(dateString, format, strict);
        },

        /**
         * @description Returns Current Date Format
         * @returns {string}
         */
        getDateFormat: function () {
            return this.dateFormat;
        },

        /**
         * @description Fetches date format setting from
         * @returns {Promise}
         */
        fetchDateFormatFromSettings: function(){
            var me = this;
            return $q.when('DD/MM/YYYY').then(function(result){
                me.dateFormat = result;
                return result;
            });
        }
    };
}

module.exports = DateFactory;
