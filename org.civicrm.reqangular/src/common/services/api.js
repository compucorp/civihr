define([
    'common/angular',
    'common/modules/apis'
], function (angular, apis) {
    'use strict';

    apis.factory('api', ['$log', '$http', '$q', '$timeout', function ($log, $http, $q, $timeout) {
        $log.debug('api');

        var API_ENDPOINT = '/civicrm/ajax/rest';

        // Draft

        return {

            /**
             * Extends the api with the given child api
             *
             * @param {object} childAPI
             * @return {object} the child api with the basic api as prototype
             */
            extend: function (childAPI) {
                return angular.extend(Object.create(this), childAPI);
            },

            /**
             * Mocks a GET request to the backend endpoints
             *
             * @param {any} result - The result the mocked request must return
             * @param {int} timeout - The value of a simulated delay in ms
             * @return {Promise}
             */
            mockGET: function (result, delay) {
                var deferred = $q.defer();

                $timeout(function() {
                  deferred.resolve(result);
                }, delay || 0);

                return deferred.promise;
            },

            /**
             * Mocks a POST request to the backend endpoints
             */
            mockPOST: function (result, delay) {
                return this.mockGET.apply(this, arguments);
            },

            /**
             * Sends a GET request to the backend endpoint
             *
             * @param {string} entity - The entity the request is asking for (Contact, Appraisal, etc)
             * @param {string} action - The action to perform
             * @param {object} params - Any additional parameters to pass in the request
             * @param {boolean} cache - If the response should be cached (default = true)
             * @return {Promise}
             */
            sendGET: function (entity, action, params, cache) {
                $log.debug('api.sendGET');

                return $http({
                    method: 'GET',
                    url: API_ENDPOINT,
                    cache: (typeof cache !== 'undefined' ? !!cache : true),
                    responseType: 'json',
                    params: {
                        sequential: 1,
                        json: JSON.stringify(params || {}),
                        entity: entity,
                        action: action
                    }
                }).then(function (response) {
                    return response.data;
                });
            },

            /**
             * Sends a POST request to the backend endpoint
             *
             * @param {string} entity - The entity the request is asking for (Contact, Appraisal, etc)
             * @param {string} action - The action to perform
             * @param {object} params - Any additional parameters to pass in the request
             * @return {Promise}
             */
            sendPOST: function (entity, action, params) {
                $log.debug('api.sendPOST');

                return $http({
                    method: 'POST',
                    url: API_ENDPOINT,
                    // This is required by the CiviCRM api endpoint
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    responseType: 'json',
                    data: {
                        json: JSON.stringify(params || {}),
                        sequential: 1,
                        entity: entity,
                        action: action
                    },
                    // AngularJS doesn't url encode the POST params automatically
                    transformRequest: function(obj) {
                        var str = [];

                        for(var p in obj) {
                            str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
                        }

                        return str.join("&");
                    },
                }).then(function (response) {
                    return response.data;
                });
            },
        };
    }]);
});
