/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('leave-absences.settings', []).constant('shared-settings', {
      attachmentToken: CRM.vars.leaveAndAbsences.attachmentToken,
      debug: CRM.debug,
      managerPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-leave/',
      sharedPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/shared/',
      serverDateFormat: 'YYYY-MM-DD',
      serverDateTimeFormat: 'YYYY-MM-DD HH:mm:ss',
      permissions: {
        admin: {
          access: 'access leave and absences',
          administer: 'administer leave and absences'
        },
        ssp: {
          access: 'access leave and absences in ssp',
          manage: 'manage leave and absences in ssp'
        }
      },
      fileUploader: {
        queueLimit: 10
      },
      statusNames: {
        approved: 'approved',
        adminApproved: 'admin_approved',
        awaitingApproval: 'awaiting_approval',
        moreInformationRequired: 'more_information_required',
        rejected: 'rejected',
        cancelled: 'cancelled'
      }
    });
  });
})(CRM);
