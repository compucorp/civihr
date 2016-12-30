(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('leave-absences.settings', []).constant('shared-settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/shared/'
    });
  });
})(CRM);
