/* eslint-env amd */

(function (CRM) {
  define(function () {
    'use strict';

    return injectHelpIcon;

    /**
     * Injects a help icon to a form field
     *
     * @param {jQuery} formClass - a class of the CiviCRM form
     * @param {String} accessNameSelector - a selector to the field to populate to
     * @param {String} title - the title of the help icon
     * @param {String} helpId - the ID of the helper
     * @param {String} helpFile - the path to the helper template
     */
    function injectHelpIcon (formClass, accessNameSelector, title, helpId, helpFile) {
      var $helpIcon;
      var $form = CRM.$('.' + formClass);
      var accessName = $form.find('[data-crm-custom="' + accessNameSelector + '"]').attr('name');
      var labelSelector = 'label[for=' + accessName + ']';

      if (!$form.length || $form.find(labelSelector + ' .helpicon').length) {
        return;
      }

      $helpIcon = CRM.$('<a href class="helpicon" title="' + title + '"></a>');

      // Populates a space before the icon if there are no other markers
      if (!$form.find(labelSelector + ' .crm-marker').length) {
        $form.find(labelSelector).append('&#160;');
      }

      $form.find(labelSelector).append($helpIcon);

      $helpIcon.on('click', function () {
        CRM.help('', { id: helpId, file: helpFile });

        return false;
      });
    }
  });
}(CRM));
