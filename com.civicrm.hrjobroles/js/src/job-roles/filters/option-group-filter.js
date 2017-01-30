define([
  'job-roles/filters/filters'
], function(filters) {
  'use strict';

  filters.filter('getActiveGroup', ['$log', function($log) {
    $log.debug('Filter: getActiveGroup');

    return function(groupData, param) {
      var filteredGroupData = [];
      angular.forEach(groupData, function(group, key) {
        if (group.is_active === param) {
          filteredGroupData.push(group);
        }
      });
      return filteredGroupData;
    }
  }]);
});
