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

  $(document).on("click", "#view-revisions", function() {
    cj("#revision-dialog").show( );
    cj("#revision-dialog").dialog({
      title: "Revisions",
      modal: true,
      width : "680px", // don't remove px
      height: "380",
      bgiframe: true,
      overlay: { opacity: 0.5, background: "black" },

      open:function() {
	var url = CRM.url('civicrm/report/instance/43', {reset:1, snippet:4, section:2, log_type_table_op:'eq', log_type_table_value:'log_civicrm_value_identify_2'}); 
        cj("#revision-content", this).load(url);
      },

      buttons: {
          "Done": function() {
          cj(this).dialog("destroy");
        }
      }
    });
  });
});
