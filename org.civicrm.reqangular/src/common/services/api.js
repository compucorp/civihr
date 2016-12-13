define([
    'common/angular',
    'common/modules/apis'
], function (angular, apis) {
    'use strict';

    apis.factory('api', ['$log', '$http', '$q', '$timeout', function ($log, $http, $q, $timeout) {
        $log.debug('api');

        var API_ENDPOINT = '/civicrm/ajax/rest';

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
             * Returns:
             *   - the list of entities, eventually filtered/paginated
             *   - the total count of the entities based on the filters,
             *     independent of the pagination settings
             *
             * @param {string} entity - The entity name
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @param {string} sort - The field and direction to order by
             * @param {object} additionalParams - Additional params to pass to the api
             * @return {Promise} resolves to an object with `list` and `total`
             */
            getAll: function (entity, filters, pagination, sort, additionalParams, action) {
                $log.debug('api.all');

                filters = filters || {};
                action = action || 'get';

                return $q.all([
                    (function () {
                        var params = _.assign({}, filters, (additionalParams || {}), {
                            options: { sort: sort || 'id DESC' }
                        });

                        if (pagination) {
                            params.options.offset = (pagination.page - 1) * pagination.size;
                            params.options.limit = pagination.size;
                        }

                        return this.sendGET(entity, action, params).then(function (data) {
                            return data.values;
                        });
                    }.bind(this))(),
                    (function () {
                        var params = _.assign({}, filters, { 'return': 'id' });

                        return this.sendGET(entity, action, params);
                    }.bind(this))()
                ]).then(function (results) {
                    return {
                        list: results[0],
                        total: results[1].count,
                        allIds: results[1].values.map(function (cycle) {
                            return cycle.id;
                        }).join(',')
                    };
                });
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
