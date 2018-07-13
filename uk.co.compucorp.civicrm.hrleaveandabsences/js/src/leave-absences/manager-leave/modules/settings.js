(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('manager-leave.settings', []).constant('settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-leave/'
    });
  });
})(CRM);
