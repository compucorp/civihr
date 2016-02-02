/**
 * This factory provides access to date format from CRM settings
 * @param $q
 * @returns {{dateFormat: null, getDateFormat: getDateFormat}}
 * @constructor
 */
function DateFormatService($q) {
    return {
        /**
         * keeps information about date format
         */
        dateFormat: null,
        /**
         * @description Fetches date format setting from TODO where?
         * @returns {Promise}
         */
        getDateFormat: function () {
            if (this.dateFormat) {
                return $q.when(this.dateFormat);
            } else {
                return $q.when('DD/MM/YYYY')
                    .then(function (result) {
                        this.dateFormat = result;
                        return result;
                    }.bind(this))
                    .catch(function (error) {
                        // Fallback to default format
                        this.dateFormat = 'YYYY-MM-DD';
                        return error;
                    }.bind(this));
            }
        }
    };
}

module.exports = DateFormatService;
