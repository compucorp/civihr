/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('manager-notification-badge.settings', []).constant('settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-notification-badge/'
    });
  });
})(CRM);
