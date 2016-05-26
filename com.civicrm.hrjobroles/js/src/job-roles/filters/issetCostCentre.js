define([
  'job-roles/filters/filters',
], function (module) {
    'use strict';

    module.filter('issetCostCentre', function() {
      return function(roles) {
        try {
          if (roles.constructor === Array) {
            return roles.filter(function (role) {
              return role.cost_centre_id !== '';
            });
          } else {
            return roles;
          }
        } catch (e) {
          return roles;
        }
      };
    });
});
