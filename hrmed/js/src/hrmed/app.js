/* eslint-env amd */

(function (CRM) {
  define(['hrcore/helpers/help-icon-injector'], function (injectHelpIcon) {
    'use strict';

    CRM.$('body').on('crmFormLoad', function () {
      injectHelpIcon(
        'custom-group-Medical_Disability',
        'Medical_Disability:Condition',
        'Condition Help',
        'hrmed-med-condition',
        'CRM/HRMed/Page/helptext');
    });
  });
}(CRM));
