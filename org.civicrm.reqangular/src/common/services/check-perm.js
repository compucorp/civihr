define([
  'common/modules/services'
], function (services) {
  'use strict';

  (function (CRM) {
    services.factory('CheckPermissions', ['$q', function ($q) {

      return {
        /**
         * Checks if currently logged in user has admin access
         *
         * @return {Promise}
         */
        canAdmin: function () {
          return $q.resolve(CRM.checkPerm('CiviHRLeaveAndAbsences: Administer Leave and Absences'));
        }
      };
    }]);
  }(CRM));
});
