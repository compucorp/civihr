/* eslint-env amd */

define(function (CRM) {
  'use strict';

  CRM.$('body').on('crmFormLoad', function () {
    var form = CRM.$('.custom-group-Career');

    if (form.length) {
      var accessName = form.find('[data-crm-custom="Career:End_Date"]').attr('name');
      var labelSelector = 'label[for=' + accessName + ']';

      if (!form.find(labelSelector + ' .helpicon').length) {
        var helpIcon = CRM.$('<a href class="helpicon" title="End Date Help"></a>');

        form.find(labelSelector).append('&#160;').append(helpIcon);
        helpIcon.on('click', function () {
          CRM.help('', {
            id: 'hrcareer-enddate',
            file: 'CRM/HRCareer/Page/helptext'
          });

          return false;
        });
      }
    }
  });
}(CRM));
