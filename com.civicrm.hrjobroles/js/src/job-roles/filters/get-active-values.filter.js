define([
  'common/lodash',
  'job-roles/filters/filters'
], function(_,filters) {
  'use strict';

  filters.filter('getActiveValues', ['$log', function($log) {
    $log.debug('Filter: getActiveValues');

    return function(optionValues) {
      var filteredOptionValues = {};

      _.each(optionValues, function (optionValue, idValue) {
        if (optionValue.is_active === '1') {
          filteredOptionValues[idValue] = optionValue;
        }
      });

      return filteredOptionValues;
    }
  }]);
});
