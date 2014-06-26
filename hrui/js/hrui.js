// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  $(document).on('crmLoad', function(e) {
    // Rename "Summary" tab to "Personal Details"
    // Hack to check contact type - This field only appears for individuals
    if ($('.crm-contact-job_title', '.crm-summary-contactinfo-block').length) {
      $('.crm-contact-tabs-list #tab_summary a', e.target).text('Personal Details');
    }

    /* Changes on Add Individual pages and Personal details tab for HR-358 */
    //Shift Source field below External Identifier
    if ($('div.crm-contact_external_identifier_label').parent('div.crm-summary-row').next('div').length < 1) {
      ($('div.crm-contact_source').parent('div.crm-summary-row')).insertAfter($('div.crm-contact_external_identifier_label').parent('div.crm-summary-row'));
    }

    // Hide current employer, website, im and job title
    // Contact summary screen:
    $('div.crm-contact-current_employer, div.crm-contact-job_title, div.crm-contact_source ', '.crm-summary-contactinfo-block').parent('div.crm-summary-row').hide();
    $('div#im-block, div#website-block, div.constituent_information').hide();

    // Inline edit form
    $('form#ContactInfo input#employer_id, form#ContactInfo input#job_title, form#ContactInfo input#contact_source', e.target).closest('div.crm-summary-row').hide();

    // Contact edit screen
    $('input#employer_id, input#job_title', 'form#Contact').parent('td').hide();
    // changes of website, email, IM block
    $('#IM_Block_1, #Website_Block_1', '.contact_information-section tbody ').next('tr').hide();
    $('#Website_Block_1, #IM_Block_1','.contact_information-section tbody').prev('tr').hide();
    $('#IM_Block_1, #Website_Block_1, #Email_Block_1 div.email-signature, #Email_Block_1 td#Email-Bulkmail-html', '.contact_information-section tbody').hide();
    $('#Email-Primary').prev('td').prev('td').hide();
    $('#Email_Block_1 td#Email-Bulkmail-html,#Email-Primary ', '.contact_information-section tbody').prev('td').hide();

    //remove bulkmail and onhold on click of add email
    $('a#addEmail').click(function() {
      $('td#Email-Bulkmail-html,div.email-signature').hide();
      $('td#Email-Bulkmail-html').prev('td').hide();
    });

    //remove longitue and billing address on click of another address
    $('#addressBlock a.button').click(function() {
      $("#addressBlock input[id$='_geo_code_1']").parent('td').hide();
      $('td#Address-Primary-html span.is_billing-address-element').hide();
    });

    //changes for notification
    $('INPUT#contact_source').parent('td').children('a').click(function() {
      $('#crm-notification-container .ui-notify-message .notify-content').remove();
      if ($('#crm-notification-container .ui-notify-message p').length < 1) {
        $('#crm-notification-container .ui-notify-message').append('<p>Source is a useful field where data has been migrated to CiviHR from one or a number of other legacy systems. The Source field will indicate which legacy system the contact has come from.</p>');
      }
    });
    //changing of address block
    $('.crm-edit-address-form .is_billing-address-element ,div#constituent_information').hide();
    $('#address_1_geo_code_1').parent('td').hide();
    $('.crm-demographics-accordion').insertAfter($('.crm-contactDetails-accordion'));
  });
}(CRM.$, CRM._));
