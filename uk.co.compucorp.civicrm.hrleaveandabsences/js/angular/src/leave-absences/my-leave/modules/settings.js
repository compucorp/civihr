(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('my-leave.settings', []).constant('settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAbsences.baseURL + '/views/my-leave/'
    });
  });
})(CRM);
