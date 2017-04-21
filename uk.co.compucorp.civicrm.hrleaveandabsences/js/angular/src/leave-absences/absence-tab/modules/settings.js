(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('absence-tab.settings', []).constant('settings', {
      contactId: CRM.vars.leaveAndAbsences.contactId,
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/absence-tab/'
    });
  });
})(CRM);
