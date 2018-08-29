/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'common/modules/models'
], function (_, moment, models) {
  'use strict';

  models.factory('Model', function () {
    return {
      compactFilters: compactFilters,
      extend: extend,
      processFilters: processFilters
    };

    /**
     * Returns the date in the format the API expects
     *
     * @param  {String} date
     * @return {String}
     */
    function apiDateFormat (date) {
      return isStandardDateFormat(date) ? date : moment(date, 'DD/MM/YYYY').format('YYYY-MM-DD');
    }

    /**
     * Removes falsy values from the filters (except 0 or false)
     *
     * @param  {Object} filters
     * @return {Object|null}
     */
    function compactFilters (filters) {
      if (!filters) {
        return null;
      }

      return _.pickBy(filters, function (value) {
        return value === 0 || value === false || !!value;
      });
    }

    /**
     * Extends the basic Model with the given ChildModel
     *
     * @param  {Object} ChildModel
     * @return {Object}
     */
    function extend (ChildModel) {
      return _.assign(Object.create(this), ChildModel);
    }

    /**
     * Checks if the given date is in the standard YYYY-MM-DD[ HH:mm[:ss]] format
     *
     * @param  {String} date
     * @return {Boolean}
     */
    function isStandardDateFormat (date) {
      var standardFormats = ['YYYY-MM-DD', 'YYYY-MM-DD HH:mm', 'YYYY-MM-DD HH:mm:ss'];

      return _.some(standardFormats, function (standardFormat) {
        return moment(date, standardFormat).format(standardFormat) === date;
      });
    }

    /**
     * Transforms date range filters to values the API can use
     *
     * @param  {Object} value
     * @return {Object}
     */
    function processDateRangeFilter (value) {
      if (value.from && value.to) {
        return { 'BETWEEN': [ apiDateFormat(value.from), apiDateFormat(value.to) ] };
      } else if (value.from) {
        return { '>=': apiDateFormat(value.from) };
      } else {
        return { '<=': apiDateFormat(value.to) };
      }
    }

    /**
     * Processes the filters provided, removing falsy values (except 0 or false)
     * And applies filter-specific transformations if needed
     *
     * @param  {Object} rawFilters - unprocessed filters
     * @return {Object|null}
     */
    function processFilters (rawFilters) {
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

    /**
     * Transforms multiple values filters to values the API can use
     *
     * @param  {Object} value
     * @return {Object}
     */
    function processMultipleValuesFilter (value) {
      if (value.in) {
        return { 'IN': value.in };
      } else {
        return { 'NOT IN': value.nin };
      }
    }
  });
});
