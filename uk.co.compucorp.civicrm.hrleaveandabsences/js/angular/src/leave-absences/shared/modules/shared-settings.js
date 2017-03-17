(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('leave-absences.settings', []).constant('shared-settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/shared/',
      managerPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-leave/',
      serverDateFormat: 'YYYY-MM-DD',
      serverDateTimeFormat: 'YYYY-MM-DD HH:mm:ss',
      attachmentToken: CRM.vars.leaveAndAbsences.attachmentToken
    });
  });
})(CRM);
