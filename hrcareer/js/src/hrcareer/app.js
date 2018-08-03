/* eslint-env amd */

(function (CRM) {
  define(['hrcore/helpers/help-icon-injector'], function (injectHelpIcon) {
    'use strict';

    CRM.$('body').on('crmFormLoad', function () {
      injectHelpIcon(
        'custom-group-Career',
        'Career:End_Date',
        'End Date Help',
        'hrcareer-enddate',
        'CRM/HRCareer/Page/helptext');
    });
  });
}(CRM));
