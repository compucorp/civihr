/* eslint-env amd */

(function (CRM) {
  define([
    'common/lodash',
    'common/modules/services'
  ], function (_, services) {
    'use strict';

    services.factory('checkPermissions', ['$q', function ($q) {
      /**
       * Checks if the currently logged in user has the given permission(s)
       * by using the `CRM.checkPerm` method
       *
       * If given multiple permissions, an AND logic is applied to the checks
       *
       * @param  {string/Array} permissions
       * @return {Promise} resolves to a {boolean}
       */
      return function (permissions) {
        permissions = _.isArray(permissions) ? permissions : [permissions];

        return $q.resolve(permissions.every(function (permission) {
          return CRM.checkPerm(permission);
        }));
      };
    }]);
  });
}(CRM));
