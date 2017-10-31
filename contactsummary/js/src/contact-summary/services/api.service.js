define([
    'contact-summary/modules/contact-summary.services'
], function (services) {
    'use strict';

    services.factory('ApiService', ['$http', '$q', function ($http, $q) {

        /**
         * @ngdoc function
         * @param entityName
         * @param data
         * @param action
         * @param stringify
         * @returns {*}
         * @private
         */
        function buildData(entityName, data, action, stringify) {
            if (!angular.isDefined(entityName)) {
                throw new Error('Entity name not provided');
            }

            if (!angular.isDefined(action)) {
                throw new Error('Action not provided');
            }

            data = angular.extend({
                entity: entityName,
                action: action,
                sequential: 1,
                json: 1,
                rowCount: 0
            }, data);

            // Because data needs to be sent as string for CiviCRM to accept
            return (!!stringify ? jQuery.param(data) : data);
        }

        /**
         * @ngdoc function
         * @param method
         * @param data
         * @param config
         * @returns {HttpPromise}
         * @private
         */
        function sendRequest(method, data, config) {
            config = angular.extend({
                method: method,
                url: '/civicrm/ajax/rest'
            }, (method === 'post' ? { data: data } : { params: data }), config);

            return $http(config)
                .then(function (response) {
                    if (response.is_error) {
                        return $q.reject(response);
                    }

                    return response.data;
                })
                .catch(function (response) {
                    return response;
                });
        }

        return {
            /**
             * @ngdoc method
             * @name get
             * @methodOf ApiService
             * @param entityName
             * @param data
             * @param config
             * @returns {*}
             */
            get: function (entityName, data, config) {
                return sendRequest('get', buildData(entityName, data, 'get'), config);
            },

            /**
             * @ngdoc method
             * @name post
             * @methodOf ApiService
             * @param entityName
             * @param data
             * @param action
             * @param config
             * @returns {HttpPromise}
             */
            post: function (entityName, data, action, config) {
                config = angular.extend({
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }, config);

                return sendRequest('post', buildData(entityName, data, action, true), config);
            },

            getValue: function (entityName, data) {
                // todo
            },

            create: function (entityName, data) {
                // todo
            },

            update: function (entityName, data) {
                // todo
            },

            delete: function (entityName, data) {
                // todo
            }
        }
    }]);
});
