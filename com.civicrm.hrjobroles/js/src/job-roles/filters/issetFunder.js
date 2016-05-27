define([
  'job-roles/filters/filters',
], function (module) {
    'use strict';

    module.filter('issetFunder', function() {
      return function(roles) {
        try {
          if (roles.constructor === Array) {
            return roles.filter(function (role) {
              return (
                role.funder_id !== '' &&
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
