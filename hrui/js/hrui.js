// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  function callGovern() {
    $.ajax({
      url: CRM.url('civicrm/contact/government/detail'),
      data: {cid: CRM.cid},
      type: 'GET',
      dataType: "json",
    }).done(function(text) {
      if (text['govTypeNumber']) {
        $('#govValID').html(text['govType']+" - "+text['govTypeNumber']);
      }
    });
  }

  var title = jQuery('.crm-form-block h2').text();
  jQuery('.page-title').text('Import - ' + title);

  $(document).on('crmLoad', function(e) {
    var isDataSourceOK = false;

    $('#data-source-form-block').on('DOMSubtreeModified', addUploadFileListener);

    /**
     * Add an event listener on #uploadFile
     */
    function addUploadFileListener() {
      if ($('#uploadFile').length === 1 && !isDataSourceOK) {
        isDataSourceOK = true;
        $('#uploadFile').on('change', insertFile);
      }
    }

    /**
     * Insert a DOM node after #uploadFile
     * with the filename
     */
    function insertFile() {
      var fileName = $(this)[0].files[0];

      $('#js-uploaded-file').remove();
      if (fileName !== undefined) {
        $(this).after('<span id="js-uploaded-file" class="uploaded-file">' + fileName.name + ' <span class="uploaded-file-icon-trash"><i class="fa fa-trash-o"></i> Remove</span>');

        $('.uploaded-file-icon-trash').on('click', removeFile);
      }
    }

    /**
     * Remove the #js-uploaded-file DIV and
     * clean #uploadFile value
     */
    function removeFile() {
      $('#js-uploaded-file').remove();
      $('#uploadFile').val('');
    }

    //change text from Client to Contact
    $('#crm-activity-view-table .crm-case-activity-view-Client .label').html('Contact');
    if (CRM.formName == 'contactForm' || CRM.pageName == 'viewSummary') {
      // Rename "Summary" tab to "Personal Details"
      // Hack to check contact type - This field only appears for individuals
      if ($('.crm-contact-job_title', '.crm-summary-contactinfo-block').length) {
        $('.crm-contact-tabs-list #tab_summary a', e.target).text('Personal Details');
      }

      //add government field
      var govfield = "<div class='container crm-summary-row' id='government'><div class='crm-label'>Government ID</div><div id='govValID' class='crm-content'></div></div>";
      if (CRM.cid && CRM.hideGId) {
        $('#row-custom_'+CRM.hideGId, e.target).hide();
        if ($('div#government').length < 1) {
          $(govfield).appendTo($('.crm-contact_type_label').parent('div'));
        }
	callGovern();
      }
      $('#govID').insertAfter($('#nick_name').parent('td')).show();
      $("#govID").wrap( "<td id='govtfield' colspan='3'></td>");

      // Hide current employer and job title
      // Contact summary screen:
      $('div.crm-contact-current_employer, div.crm-contact-job_title', '.crm-summary-contactinfo-block').parent('div.crm-summary-row').hide();
      // Inline edit form
      $('form#ContactInfo input#employer_id, form#ContactInfo input#job_title', e.target).closest('div.crm-summary-row').hide();

      // Contact edit screen
      $('input#employer_id, input#job_title', 'form#Contact').parent('td').hide();

      /* Changes on Add Individual pages and Personal details tab for HR-358 */
      // Move Job summary to top
      $('.HRJobContract_Summary', e.target).insertBefore($('.crm-summary-contactinfo-block'));
      // changes of email block, remove bulkmail and onhold
      $('div.email-signature, td#Email-Bulkmail-html', 'form#Contact').hide();
      $('#Email-Primary', 'form#Contact').prev('td').prev('td').hide();
      $('td#Email-Bulkmail-html, #Email-Primary', 'form#Contact').prev('td').hide();

      //shift demographic above extended demographic
      $('.crm-demographics-accordion', 'form#Contact').insertAfter($('.crm-contactDetails-accordion'));
      if ($('tr#Phone_Block_2', 'form#Contact').length < 1) {
        $('#addPhone').click();
      }
    }
    $('span.crm-frozen-field', '.crm-profile-name-hrident_tab').closest('div').parent('div').hide();
    //changes of sorce help text
    $('INPUT#contact_source').parent('td').children('a').click(function() {
      $('#crm-notification-container .crm-help .notify-content').remove();
      if ($('#crm-notification-container .crm-help p').length) {
	$('#crm-notification-container .crm-help p').remove();
      }
      $('#crm-notification-container .crm-help').append('<p>Source is a useful field where data has been migrated to CiviHR from one or a number of other legacy systems. The Source field will indicate which legacy system the contact has come from.</p>');
    });
  });
}(CRM.$, CRM._));
