define([
    'common/angular',
    'common/modules/angularDate'
], function (angular, module) {
    'use strict';

    /**
     * This service provides access to date format from CRM settings
     */
    module.factory('DateFormatService', ['$q', function ($q) {
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
                        .catch(function () {
                            // Fallback to default format
                            this.dateFormat = 'YYYY-MM-DD';
                            return this.dateFormat;
                        }.bind(this));
                }
            }
        };
    }]);
});
