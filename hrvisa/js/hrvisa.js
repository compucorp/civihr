cj(function($) {
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
});
