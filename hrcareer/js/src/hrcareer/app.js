/* eslint-env amd */

(function ($) {
  define([
    'hrcore/helpers'
  ], function (helpers) {
    'use strict';

    $('body').on('crmFormLoad', function () {
      var $label = helpers.getCiviCRMFormLabel(
        'custom-group-Career', 'Career:End_Date');

      if (!$label.length || $label.find('.helpicon').length) {
        return;
      }

      $label.append('&#160;'); // Populates a space before the icon
      helpers.appendHelpIcon(
        $label,
        'End Date Help',
        'hrcareer-enddate',
        'CRM/HRCareer/Page/helptext');
    });
  });
}(CRM.$));
