/* eslint-env amd */

(function (CRM, $) {
  define(function () {
    'use strict';

    return {
      appendHelpIcon: appendHelpIcon,
      getCiviCRMFormLabel: getCiviCRMFormLabel
    };

    /**
     * Appends a help icon to a given element
     *
     * @param {jQuery} $element
     * @param {String} title the title of the help icon
     * @param {String} helpId the ID of the helper
     * @param {String} helpFile the path to the helper template
     */
    function appendHelpIcon ($element, title, helpId, helpFile) {
      var $helpIcon = $('<a href class="helpicon" title="' + title + '"></a>');

      $element.append($helpIcon);

      $helpIcon.on('click', function () {
        CRM.help('', { id: helpId, file: helpFile });

        return false;
      });
    }

    /**
     * Gets a CiviCRM form field label
     *
     * @param  {String} formClass a class of the CiviCRM form
     * @param  {String} accessNameSelector a selector to the field to populate to
     * @return {jQuery}
     */
    function getCiviCRMFormLabel (formClass, accessNameSelector) {
      var $form = $('.' + formClass);
      var accessName = $form.find('[data-crm-custom="' + accessNameSelector + '"]').attr('name');
      var labelSelector = 'label[for=' + accessName + ']';

      return $form.find(labelSelector);
    }
  });
}(CRM, CRM.$));
