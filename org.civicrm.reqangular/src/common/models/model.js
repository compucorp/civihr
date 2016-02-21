define([
    'common/lodash',
    'common/moment',
    'common/modules/models'
], function (_, moment, models) {
    'use strict';

    models.factory('Model', function () {

        /**
         * Transforms date range filters to values the API can use
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

            filters[field] = filters[field] || {};
            filters[field][operator] = moment(value, 'DD/MM/YYYY').format('YYYY-MM-DD');;
        }

        return {

            /**
             * Extends the basic Model with the given ChildModel
             *
             * @param {object} ChildModel
             * @return {object}
             */
            extend: function (ChildModel) {
                return _.assign(Object.create(this), ChildModel);
            },

            /**
             * Processes the filters provided, removing falsey values (except 0 or false)
             * And applying filter-specific transformations if needed
             *
             * @param {object} rawFilters - The unprocessed filters
             * @return {object|null}
             */
            processFilters: function (rawFilters) {
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
            }
        };
    });
});
