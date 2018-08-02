/* eslint-env amd */

define(function (CRM) {
  'use strict';

  CRM.$('body').on('crmFormLoad', function () {
    var form = CRM.$('.custom-group-Medical_Disability');

    if (form.length) {
      var accessName = form.find('[data-crm-custom="Medical_Disability:Condition"]').attr('name');
      var labelSelector = 'label[for=' + accessName + ']';

      if (!form.find(labelSelector + ' .helpicon').length) {
        var helpIcon = CRM.$('<a href class="helpicon" title="Condition Help"></a>');

        form.find(labelSelector).append(helpIcon);
        helpIcon.on('click', function () {
          CRM.help('', {
            id: 'hrmed-med-condition',
            file: 'CRM/HRMed/Page/helptext'
          });

          return false;
        });
      }
    }
  });
}(CRM));
