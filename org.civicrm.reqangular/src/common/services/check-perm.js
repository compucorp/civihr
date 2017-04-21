define([
  'common/modules/services'
], function (services) {
  'use strict';

  (function (CRM) {
    services.factory('CheckPermissions', function () {

      /**
       * Checks if currently logged in user has admin access
       */
      return {
        canAdmin: function () {
          return CRM.checkPerm('CiviHRLeaveAndAbsences: Administer Leave and Absences');
        }
      }
    })
  }(CRM));
});
