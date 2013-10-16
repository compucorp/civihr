// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
// js to hide current employer and job title from contact view screen
cj(document).ready(function($) {
  cj('.crm-contact-current_employer').parent('div.crm-summary-row').hide();
  cj('.crm-contact-job_title').parent('div.crm-summary-row').hide();
 
  //rename "Summary" tab to "Personal Details"
  $('#tab_summary a').text('Personal Details');

  // to make Emergency Contact is as default selected
  if (CRM.hremerg) {
    cj("select#relationship_type_id option[value='" + CRM.hremerg.relationshipTypeId + "_a_b']").attr('selected',true);
  }
  var relationshipTypeValue = cj( "#relationship_type_id option:selected" ).val();
  var contactAutocomplete   = cj('#contact_1');
  // always reset field so that correct data url is linked
  contactAutocomplete.unautocomplete( );
  // to enable create new contact dropdown and select contact autocomplete which is disabled on form load
  if ( relationshipTypeValue ) {
    cj('#profiles_1').attr('disabled', false);
    contactAutocomplete.attr('disabled', false);
    contactAutocomplete.addClass('ac_input');
    buildCreateNewSelect( 'profiles_1', relationshipTypeValue );
    var dataUrl =   CRM.url('civicrm/ajax/rest', 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=relationship&rel=') + relationshipTypeValue;
    contactAutocomplete.autocomplete( dataUrl, { width : 200, selectFirst : false, matchContains: true,} );
    contactAutocomplete.result(function( event, data ) {
      cj("input[name='contact_select_id[1]']").val(data[1]);
      cj('#relationship-refresh-save').show( );
      buildRelationFields(relationshipTypeValue);
    });
  } 
  else {
    cj('#profiles_1').attr('disabled', true);
    contactAutocomplete.removeClass('ac_input');
    contactAutocomplete.attr('disabled', true);
    contactAutocomplete.unautocomplete( );
  }
  // hide Relationship dropdown
  cj('.crm-relationship-form-block .crm-relationship-form-block-relationship_type_id').hide();
});

// for inline edit
cj(document).ajaxSuccess(function() {
  cj('#current_employer').parent('div.crm-content').parent('div.crm-summary-row').hide();
  cj('#job_title').parent('div.crm-content').parent('div.crm-summary-row').children('div.crm-label').children('label[for="job_title"]').parent().hide();
  cj('#job_title').parent('div.crm-content').hide();;
});
// for contact edit screen
cj(document).ready(function($) {
    cj('#current_employer').parent('td').children().remove();
    cj('#job_title').parent('td').hide();
});
