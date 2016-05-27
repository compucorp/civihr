define([
  'job-roles/services/services',
], function (module) {
  'use strict';

  module.factory('issetCostCentre', function() {
    return function(roles) {
      try {
        if (roles.constructor === Array) {
          return roles.filter(function (role) {
            return (
              role.cost_centre_id !== '' &&
              ((role.type === '1' && parseInt(role.percentage) > 0) ||
              (role.type === '0' && parseInt(role.amount) > 0))
            );
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
