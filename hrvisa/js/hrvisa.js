// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.$(function($) {
  $(document).on("click", "#is_visa_required", function() {
    var fieldName = $(this).parent().attr('id').replace('_is_visa_required', '');

    //params to be passed in the api
    var params = $.parseJSON('{"sequential": "1"}');
    params["entity_id"] = CRM.hrvisa.contactID;
    params[fieldName] = $(this).is(":Checked") ? 1 : 0;
    CRM.api('CustomValue', 'create', params,{
      success: function(data) {
        if (data.is_error == '0') {
          params[fieldName] ? CRM.alert("Visa is Required", '', 'success') : CRM.alert("Visa is Not Required", '', 'success');
        }
      }
    });
  });

  //hide "is visa required" checkbox that appears within the profile dialog
  $(document).on("click", ".crm-profile-name-hrvisa_tab .action-item", function() {
    $(document).ajaxSuccess(function() {
      var parentID = $('#is_visa_required').parent().attr('id');
      $('#profile-dialog #' + parentID).hide();
    });
  });

  // add helpicon for conitions
  $('body').on('crmFormLoad', function(event) {
    if (event.profileName == 'hrvisa_tab') {
      var accessName = $('[data-crm-custom="Immigration:Conditions"]').attr('name');
      if($('div#editrow-' + accessName + ' a.helpicon').length == 0) {
        var helpIcon = $( "<span class ='crm-container'><a class='helpicon' onclick='CRM.help(\"\", {\"id\":\"hrvisa-condition\",\"file\":\"CRM\/HRVisa\/Page\/helptext\"}); return false;' title='Conditions Help'></a></span>" );
        $('div#editrow-' + accessName +' div label').append(helpIcon);
      }
    }
  });
});
