// http://civicrm.org/licensing
CRM.$(function ($) {
  $('body').on('crmFormLoad', function (event) {
    if (event.profileName === 'hrcareer_tab') {
      var accessName = $('[data-crm-custom="Career:End_Date"]').attr('name');
      if ($('div#editrow-' + accessName + ' a.helpicon').length === 0) {
        var helpIcon = $("<span class ='crm-container'><a class='helpicon' onclick='CRM.help(\"\", {\"id\":\"hrcareer-enddate\",\"file\":\"CRM/HRCareer/Page/helptext\"}); return false;' title='End Date Help'></a></span>");
        $('div#editrow-' + accessName + ' div label').append(helpIcon);
      }
    }
  });
});
