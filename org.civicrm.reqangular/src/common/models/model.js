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
          return isStandardDateFormat(date) ? date : moment(date, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }

        /**
         * Checks if the given date is in the standard YYYY-MM-DD format
         *
         * @param {string} date
         * @return {boolean}
         */
        function isStandardDateFormat(date) {
          return moment(date, 'YYYY-MM-DD').format('YYYY-MM-DD') === date;
        }

        /**
         * Transforms date range filters to values the API can use
         *
         * @param {object} value
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

        /**
         * Transforms multiple values filters to values the API can use
         *
         * @param {object} value
         * @return {object}
         */
        function processMultipleValuesFilter(value) {
            if (value.in) {
                return { 'IN': value.in };
            } else {
                return { 'NOT IN': value.nin };
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
             * Removes falsey values from the filters (except 0 or false)
             *
             * @param {object} filters
             * @return {object|null}
             */
            compactFilters: function (filters) {
                if (!filters) {
                    return null;
                }

                return _.pick(filters, function (value) {
                    return value === 0 || value === false || !!value;
                });
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

                rawFilters = this.compactFilters(rawFilters);

                return _.transform(rawFilters, function (filters, value, key) {
                    if (value.from || value.to) {
                        filters[key] = processDateRangeFilter(value);
                    } else if (value.in || value.nin) {
                        filters[key] = processMultipleValuesFilter(value);
                    } else {
                        filters[key] = value;
                    }
                }, {});
            }
        };
    });
});
