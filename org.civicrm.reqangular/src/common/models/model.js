define([
    'common/lodash',
    'common/moment',
    'common/modules/models'
], function (_, moment, models) {
    'use strict';

    models.factory('Model', function () {

        /**
         * Uses the date format the API expects
         *
         * @param {string} date
         * @return {string}
         */
        function apiDateFormat(date) {
            return moment(date, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }

        /**
         * Transforms date range filters to values the API can use
         *
         * @param {string} value
         * @return {object}
         */
        function processDateRangeFilter(value) {
            if (value.from && value.to) {
                return { 'BETWEEN': [ apiDateFormat(value.from), apiDateFormat(value.to) ] };
            } else if (value.from) {
                return { '>=': apiDateFormat(value.from) };
            } else {
                return { '<=': apiDateFormat(value.to) };
            }
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
                    .transform(function (filters, value, key) {
                        if (value.from || value.to) {
                            filters[key] = processDateRangeFilter(value);
                        } else {
                            filters[key] = value;
                        }
                    }, {})
                    .value();
            }
        };
    });
});
