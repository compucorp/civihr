(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('leave-absences.settings', []).constant('shared-settings', {
      attachmentToken: CRM.vars.leaveAndAbsences.attachmentToken,
      debug: CRM.debug,
      managerPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-leave/',
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/shared/',
      serverDateFormat: 'YYYY-MM-DD',
      serverDateTimeFormat: 'YYYY-MM-DD HH:mm:ss',
      fileUploader: {
        //TODO for now set the limit to 10 files until a better solution is found to configure it
        queueLimit: 10,
        //set the mime types which are allowed to be uploaded as attachments
        allowedMimeTypes: ['plain', 'png', 'jpeg', 'bmp', 'gif', 'pdf', 'msword', 'vnd.openxmlformats-officedocument.wordprocessingml.document', 'vnd.ms-excel', 'vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'vnd.ms-powerpoint', 'vnd.openxmlformats-officedocument.presentationml.presentation']
      }
    });
  });
})(CRM);
