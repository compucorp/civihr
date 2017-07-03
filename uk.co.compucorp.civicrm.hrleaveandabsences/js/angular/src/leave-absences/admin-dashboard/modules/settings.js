/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('admin-dashboard.settings', []).constant('settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/admin-dashboard/'
    });
  });
})(CRM);
