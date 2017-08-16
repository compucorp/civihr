/* eslint-env amd */

define([
  'common/lodash',
  'job-roles/modules/job-roles.filters'
], function (_, filters) {
  'use strict';

  filters.filter('getActiveValues', getActiveValues);

  getActiveValues.$inject = ['$log'];

  function getActiveValues ($log) {
    $log.debug('Filter: getActiveValues');

    return function (optionValues) {
      var filteredOptionValues = {};

      _.each(optionValues, function (optionValue, idValue) {
        if (optionValue.is_active === '1') {
          filteredOptionValues[idValue] = optionValue;
        }
      });

      return filteredOptionValues;
    };
  }
});
