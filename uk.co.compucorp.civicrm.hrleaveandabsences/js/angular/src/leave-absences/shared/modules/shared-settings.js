(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('leave-absences.settings', []).constant('shared-settings', {
      attachmentToken: CRM.vars.leaveAndAbsences.attachmentToken,
      debug: CRM.debug,
      managerPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-leave/',
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/shared/',
      reqAngular: CRM.vars.reqAngular,
      serverDateFormat: 'YYYY-MM-DD',
      serverDateTimeFormat: 'YYYY-MM-DD HH:mm:ss'
    });
  });
})(CRM);
