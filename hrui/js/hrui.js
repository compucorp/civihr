// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  $(document).on('crmLoad', function(e) {
    // Rename "Summary" tab to "Personal Details"
    // Hack to check contact type - This field only appears for individuals
    if ($('.crm-contact-job_title', '.crm-summary-contactinfo-block').length) {
      $('.crm-contact-tabs-list #tab_summary a', e.target).text('Personal Details');
    }
    // Hide current employer and job title
    // Contact summary screen:
    $('div.crm-contact-current_employer, div.crm-contact-job_title', '.crm-summary-contactinfo-block').parent('div.crm-summary-row').hide();
    // Contact edit screen
    $('input#employer_id, input#job_title', 'form#Contact').parent('td').hide();
    // Inline edit form
    $('form#ContactInfo input#employer_id, form#ContactInfo input#job_title', e.target).closest('div.crm-summary-row').hide();
  });
}(CRM.$, CRM._));
