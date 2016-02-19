define([
    'common/modules/apis',
    'common/services/api'
], function (apis) {
    'use strict';

    apis.factory('api.appraisal', ['$log', 'api', function ($log, api) {
        $log.debug('api.appraisal');

        return api.extend({

            /**
             * Returns:
             *   - the list of appraisals, eventually filtered/paginated
             *   - the total count of the appraisals based on the filters,
             *     independent of the pagination settings
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @param {string} sort - The field and direction to order by
             * @return {Promise} resolves to an object with `list` and `total`
             */
            all: function (filter, pagination, sort) {
                $log.debug('api.appraisal.api');

                filters = filters || {};

                return $q.all([
                    (function () {
                        var params = _.assign({}, filters, {
                            options: { sort: sort || 'id DESC' }
                        });

                        if (pagination) {
                            params.options.offset = (pagination.page - 1) * pagination.size;
                            params.options.limit = pagination.size;
                        }

                        return this.sendGET('Appraisal', 'get', params).then(function (data) {
                            return data.values;
                        });
                    }.bind(this))(),
                    (function () {
                        var params = _.assign({}, filters, { 'return': 'id' });

                        return this.sendGET('Appraisal', 'get', params);
                    }.bind(this))()
                ]).then(function (results) {
                    return {
                        list: results[0],
                        total: results[1].count,
                        allIds: results[1].values.map(function (appraisal) {
                            return appraisal.id;
                        }).join(',')
                    };
                });
            },

            /**
             * Finds the appraisal with the given id
             *
             * @param {string/int} id
             * @return {Promise} resolves to the found appraisal
             */
            find: function (id) {
                $log.debug('api.appraisal.find');

                return this.sendGET('Appraisal', 'get', { id: '' + id }, false)
                    .then(function (data) {
                        return data.values[0];
                    });
            }
        });
    }]);
});
