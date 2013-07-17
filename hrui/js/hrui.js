// js to hide current employer and job title from contact view screen
cj(document).ready(function($) {
  cj('.crm-contact-current_employer').parent('div.crm-summary-row').hide();
  cj('.crm-contact-job_title').parent('div.crm-summary-row').hide();

  //rename "Summary" tab to "Contact Details"
  $('#tab_summary a').text('Contact Details');
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
