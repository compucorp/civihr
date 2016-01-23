define([
    'common/angular',
    'common/lodash',
    'common/modules/apis',
    'common/services/api',
    'common/services/api/option-group'
], function (angular, _, apis) {
    'use strict';

    apis.factory('api.appraisals', ['$q', '$log', 'api', 'api.optionGroup', function ($q, $log, api, optionGroupAPI) {
        $log.debug('api.appraisals');

        // Draft

        return api.extend({

            /**
             * Returns:
             *   - the list of cycles, eventually filtered/paginated
             *   - the total count of the cycles based on the filters,
             *     independent of the pagination settings
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @param {string} sort - The field and direction to order by
             * @return {Promise} resolves to an object with `list` and `total`
             */
            all: function (filters, pagination, sort) {
                $log.debug('api.appraisals.all');

                filters = filters || {};

                return $q.all([
                    (function () {
                        var params = _.assign({}, filters, {
                            'api.AppraisalCycle.getappraisalsperstep': {},
                            options: { sort: sort || 'id DESC' }
                        });

                        if (pagination) {
                            params.options.offset = (pagination.page - 1) * pagination.size;
                            params.options.limit = pagination.size;
                        }

                        return this.sendGET('AppraisalCycle', 'get', params).then(function (data) {
                            return data.values;
                        });
                    }.bind(this))(),
                    (function () {
                        var params = _.assign({}, filters, { 'return': 'id' });

                        return this.sendGET('AppraisalCycle', 'get', params);
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
             * Creates a new appraisal cycle
             *
             * @param {object} attributes - The data of the new cycle
             * @return {Promise} resolves to the newly created cycle
             */
            create: function (attributes) {
                $log.debug('api.appraisals.create');

                return this.sendPOST('AppraisalCycle', 'create', _.assign(attributes, {
                        'api.AppraisalCycle.getappraisalsperstep': {}
                    }))
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Finds the appraisal cycle with the given id
             *
             * @param {string/int} id
             * @return {Promise} resolves to the found cycle
             */
            find: function (id) {
                $log.debug('api.appraisals.find');

                var params = {
                    id: '' + id,
                    'api.AppraisalCycle.getappraisalsperstep': {}
                };

                return this.sendGET('AppraisalCycle', 'get', params, false).then(function (data) {
                    return data.values[0];
                });
            },

            /**
             * # TO DO #
             */
            grades: function () {
                $log.debug('grades');

                return this.mockGET([
                    { label: 1, value: 17 },
                    { label: 2, value: 74 },
                    { label: 3, value: 90 },
                    { label: 4, value: 30 }
                ]);
            },

            /**
             * Returns the list of all currently active status for an appraisal cycle
             *
             * @return {Promise}
             */
            statuses: function () {
                $log.debug('api.appraisals.statuses');

                return optionGroupAPI.valuesOf('appraisal_status');
            },

            /**
             * Returns the status overview re distribution of appraisals by step
             *
             * @param {object} params
             * @return {Promise}
             */
            statusOverview: function (params) {
                $log.debug('api.appraisals.statusOverview');

                return this.sendGET('AppraisalCycle', 'getstatusoverview', params)
                    .then(function (data) {
                        return data.values;
                    });
            },

            /**
             * Updates an appraisal cycle
             *
             * @param {object} attributes - The new data of the cycle
             * @return {Promise} resolves to the amended cycle
             */
            update: function (attributes) {
                $log.debug('api.appraisals.update');

                return this.sendPOST('AppraisalCycle', 'create', _.assign(attributes, {
                        'api.AppraisalCycle.getappraisalsperstep': {}
                    }))
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Returns the total number of appraisal cycles
             *
             * @param {object} filters - The filters to apply to the count
             * @return {Promise} resolves to an integer
             */
            total: function (filters) {
                $log.debug('api.appraisals.total');

                return this.sendGET('AppraisalCycle', 'getcount', filters || {}).then(function (data) {
                    return data.result;
                });
            },

            /**
             * Returns the list of all currently active types for an appraisal cycle
             *
             * @return {Promise}
             */
            types: function () {
                $log.debug('api.appraisals.types');

                return optionGroupAPI.valuesOf('appraisal_cycle_type');
            }
        });
    }]);
});
