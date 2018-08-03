/* eslint-env amd */

(function ($) {
  define([
    'hrcore/helpers'
  ], function (helpers) {
    'use strict';

    $('body').on('crmFormLoad', function () {
      var $label = helpers.getCiviCRMFormLabel(
        'custom-group-Medical_Disability', 'Medical_Disability:Condition');

      if (!$label.length || $label.find('.helpicon').length) {
        return;
      }

      helpers.appendHelpIcon(
        $label,
        'Condition Help',
        'hrmed-med-condition',
        'CRM/HRMed/Page/helptext');
    });
  });
}(CRM.$));
