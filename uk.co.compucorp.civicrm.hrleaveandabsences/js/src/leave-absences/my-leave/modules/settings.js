(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('my-leave.settings', []).constant('settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/my-leave/'
    });
  });
})(CRM);
