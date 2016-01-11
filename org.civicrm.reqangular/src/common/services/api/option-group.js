define([
    'common/angular',
    'common/modules/apis',
    'common/services/api'
], function (angular, apis) {
    'use strict';

    apis.factory('api.optionGroup', ['$log', 'api', function ($log, api) {
        $log.debug('api.optionGroup');

        /**
         * Returns the id of the option group with the given name
         *
         * @param {string} name
         * @return {Promise} resolves to the id
         */
        function idOf(name) {
            return this.sendGET('OptionGroup', 'getvalue', {
                name: name,
                return: 'id'
            })
            .then(function (data) {
                return data.result;
            });
        }

        return api.extend({

            /**
             * Returns the values of the option group with the given name
             *
             * @param {string} name
             * @param {object} optional parameters for the query
             * @return {Promise} resolves to an array with the values
             */
            valuesOf: function (name, params) {
                return idOf.call(this, name)
                    .then(function (groupId) {
                        return this.sendGET('OptionValue', 'get', angular.extend({
                            option_group_id: groupId,
                            is_active: '1'
                        }, params));
                    }.bind(this))
                    .then(function (data) {
                        return data.values;
                    });
            }
        });
    }]);
});
