/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/apis',
  'common/services/api'
], function (_, apis) {
  'use strict';

  apis.factory('api.optionGroup', ['$log', 'api', function ($log, api) {
    $log.debug('api.optionGroup');

    /**
     * Normalizes the response values to hide implementation details of the API
     *
     * @param  {Object} response
     * @return {Array} a copy of the values with normalized data
     */
    function normalizeResponse (response) {
      return response.values.map(function (optionValue) {
        var valueCopy = _.clone(optionValue);

        valueCopy.option_group_name = valueCopy['option_group_id.name'];
        delete valueCopy['option_group_id.name'];

        return valueCopy;
      });
    }

    /**
     * Groups OptionValues by OptionGroup name
     *
     * @param {array} optionValues
     * @return {object} an object formed as such:
     *  {
     *    group_name_1: [ { value_1: value_1 }, { ... }, { value_n: value_n } ],
     *    ...
     *    group_name_n: [ { value_1: vlaue_1 }, { ... }, { value_n: value_n } ]
     *  }
     */
    function valuesByGroup (optionValues) {
      return _.transform(optionValues, function (result, optionValue) {
        var optionGroup = optionValue.option_group_name;

        result[optionGroup] = result[optionGroup] || [];
        result[optionGroup].push(optionValue);
      });
    }

    return api.extend({

      /**
       * Returns the values of the option groups with the given names
       *
       * @param {string/array} groupNames
       *   If the value is an array of names, the method will group
       *   the values by option group names
       * @param {object} params optional parameters for the query
       * @return {Promise}
       *   Resolves to an array with the values (if `groupNames` is a string)
       *   or an object with keys as the group names and values as the
       *   array of their option values (if `groupNames` is an array)
       */
      valuesOf: function (groupNames, params) {
        var multiple = _.isArray(groupNames);

        return this.sendGET('OptionValue', 'get', _.assign({
          'option_group_id.name': { 'IN': multiple ? groupNames : [groupNames] },
          'is_active': '1',
          'return': [
            'option_group_id.name', 'option_group_id', 'id', 'name', 'label',
            'value', 'weight', 'is_active', 'is_reserved'
          ]
        }, params))
        .then(normalizeResponse)
        .then(function (optionValues) {
          return multiple ? valuesByGroup(optionValues) : optionValues;
        });
      }
    });
  }]);
});
