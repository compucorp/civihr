define([
  'common/lodash',
  'job-roles/filters/filters'
], function(_,filters) {
  'use strict';

  filters.filter('getActiveValues', ['$log', function($log) {
    $log.debug('Filter: getActiveValues');

    return function(groupData) {
      var filteredGroupData = [];

      _.each(groupData, function (group) {
        if (group.is_active === '1') {
          filteredGroupData.push(group);
        }
      });

      return filteredGroupData;
    }
  }]);
});
