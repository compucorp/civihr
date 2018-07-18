define([
    'common/angular',
    'common/modules/angular-date',
    'common/services/hr-settings'
], function (angular, module) {
    'use strict';

    /**
     * This service provides access to date format from CRM settings
     */
    module.factory('DateFormat', ['$q', 'HR_settings', function ($q, HR_settings) {
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
                    return $q.when('dd/MM/yyyy')
                        .catch(function () {
                            // Fallback to default format
                            return 'yyyy-MM-dd';
                        })
                        .then(function(result){
                            // Save the current value in HR_settings
                            HR_settings.DATE_FORMAT = result;
                            this.dateFormat = result;

                            return result;
                        }.bind(this));
                }
            }
        };
    }]);
});
