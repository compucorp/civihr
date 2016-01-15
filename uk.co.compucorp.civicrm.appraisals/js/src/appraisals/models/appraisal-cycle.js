define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/models',
    'common/services/api/appraisals'
], function (_, moment, models) {
    'use strict';

    models.factory('AppraisalCycle', ['api.appraisals', 'AppraisalCycleInstance', function (appraisalsAPI, instance) {

        // Draft

        /**
         * Transform date range filters to values the API can use
         *
         * Date range filters come in the `a_date_from` and `a_date_to` format
         * The suffix gets stripped from the filter name and, depending on its value,
         * the correct operator is applied to the filter
         *
         * @param {string} key
         * @param {string} value
         * @param {object} filters (by reference)
         *   The current collection of processed filters
         */
        function processDateRangeFilter(key, value, filters) {
            var suffix = _.last(key.split('_'));
            var field = key.replace('_' + suffix, '');
            var operator = suffix === 'from' ? '>=' : '<=';

            filters[field] = {};
            filters[field][operator] = moment(value, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }

        /**
         * Processes the filters provided, removing falsey values (except 0 or false)
         * And applying filter-specific transformations if needed
         *
         * @param {object} rawFilters - The unprocessed filters
         * @return {object}
         */
        function processFilters(rawFilters) {
            if (!rawFilters) {
                return null;
            }

            return _.chain(rawFilters)
                .pick(function (value) {
                    return value === 0 || value === false || !!value;
                })
                .transform(function (filters, __, key) {
                    if (_.endsWith(key, '_from') || _.endsWith(key, '_to')) {
                        processDateRangeFilter(key, rawFilters[key], filters);
                    } else {
                        filters[key] = rawFilters[key];
                    }
                }, {})
                .value();
        };

        return {

            /**
             * Returns the active cycles
             *
             * @return {Promise}
             */
            active: function () {
                return appraisalsAPI.total({ cycle_is_active: true });
            },

            /**
             * Returns a list of appraisal cycles, each converted to a model instance
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @return {Promise}
             */
            all: function (filters, pagination) {
                return appraisalsAPI.all(processFilters(filters), pagination).then(function (response) {
                    response.list = response.list.map(function (cycle) {
                        return instance.init(cycle, true);
                    });

                    return response;
                });
            },

            /**
             * Creates a new appraisal cycle
             *
             * @param {object} attributes - The attributes of the cycle to be created
             * @return {Promise} - Resolves with the new cycle
             */
            create: function (attributes) {
                var cycle = instance.init(attributes).toAPI();

                return appraisalsAPI.create(cycle).then(function (newCycle) {
                    return instance.init(newCycle, true);
                });
            },

            /**
             * Finds an appraisal cycle by id
             *
             * @param {string} id
             * @return {Promise} - Resolves with the new cycle
             */
            find: function (id) {
                return appraisalsAPI.find(id).then(function (cycle) {
                    return instance.init(cycle, true);
                });
            },

            /**
             * Returns the grades data
             *
             * @return {Promise}
             */
            grades: function () {
                return appraisalsAPI.grades();
            },

            /**
             * Returns the list of all possible appraisal cycle statuses
             *
             * @return {Promise}
             */
            statuses: function () {
                return appraisalsAPI.statuses().then(function (statuses) {
                    return statuses.map(function (status) {
                        return _.pick(status, ['value', 'label']);
                    });
                });
            },

            /**
             * Returns the full appraisal cycles status overview
             *
             * @return {Promise}
             */
            statusOverview: function () {
                return appraisalsAPI.statusOverview();
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            total: function () {
                return appraisalsAPI.total();
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            types: function () {
                return appraisalsAPI.types().then(function (types) {
                    return types.map(function (types) {
                        return _.pick(types, ['value', 'label']);
                    });
                });
            },
        };
    }]);
});
