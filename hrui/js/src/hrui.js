// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {

  /**
   * Adds the Government ID field on the Personal Details page andon the Edit form
   */
  function addGovernmentIdField(target) {
    if (CRM.cid && CRM.hideGId) {
      $('.Inline_Custom_Data .crm-inline-edit').each(function() {
        $(this).detach();
        $(this).appendTo($('.crm-contact_type_label').parent('div'));
      });

      $('.Inline_Custom_Data').hide();
      $('#row-custom_'+CRM.hideGId, target).hide();
    }

    if ($('#customFields').length < 1) {
      $('#Inline_Custom_Data label').each(function() {
        $('#nick_name').parent().after('<td id="customFields"></td>');
        var nodeID = $(this).attr('for');
        var customField = $('#' + nodeID).detach();
        $('#customFields').append($(this));
        $('#customFields').append(customField);
      });

      $('#Inline_Custom_Data').remove();
    }
  }

  /**
   * Misc changes to the page (hiding elements, inserting new ones, etc)
   */
  function miscPageChanges(target) {
    //Hide current employer and job title
    // Contact summary screen:
    $('div.crm-contact-current_employer, div.crm-contact-job_title', '.crm-summary-contactinfo-block').parent('div.crm-summary-row').hide();
    // Inline edit form
    $('form#ContactInfo input#employer_id, form#ContactInfo input#job_title', target).closest('div.crm-summary-row').hide();
    // Contact edit screen
    $('input#employer_id, input#job_title', 'form#Contact').parent('td').hide();

    /* Changes on Add Individual pages and Personal details tab for HR-358 */
    // Move Job summary to top
    $('.HRJobContract_Summary', target).insertBefore($('.crm-summary-contactinfo-block'));
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

  function fetchGovernmentId() {
    return $.ajax({
      url: CRM.url('civicrm/contact/government/detail'),
      data: { cid: CRM.cid },
      type: 'GET',
      dataType: "json",
    });
  }

  /**
   * Update label 'for' attr to works with the datepicker
   *
   * @param  {jQuery object} $line [datepicker's line parent]
   */
  function linkLabelToDatepickerInput($line) {
    $line.find('label').attr('for', $line.find('.crm-form-date').attr('id'));
  }

  /**
   * Add an event listener on input[type="file"]
   * @param {jQuery Object} selector [selector from input file]
   */
  function addUploadFileListener(selector) {
    if ($(selector).length === 1) {
      $(selector).on('change', insertFile);
    }
  }

  /**
   * Insert a DOM node after input[type="file"]
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
   * clean input[type="file"] value
   */
  function removeFile() {
    var $input = $('#js-uploaded-file').parent().find('input[type="file"]');

    $('#js-uploaded-file').remove();
    $input.val('');
  }

  $('.CRM_HRRecruitment_Form_Application').addClass('crm-form-block');
  $('.CRM_HRRecruitment_Form_Application .crm-profile-name-application_profile').addClass('form-layout-compressed');

  $(document).on('crmLoad', function(e) {
    $('#activityCustomData').attr('colspan', 3);

    addUploadFileListener('#custom_87');

    $('.crm-accordion-header.crm-master-accordion-header').on('click', function() {
      window.setTimeout(function() {
        Array.prototype.forEach.call(document.querySelectorAll('.listing-box'), function(element) {
          Ps.initialize(element);
        });
      }, 0);
    });

    if ($('.CRM_HRRecruitment_Form_HRVacancy').length === 1) {
      linkLabelToDatepickerInput($('label[for="start_date"]').parents('tr'));
      linkLabelToDatepickerInput($('label[for="end_date"]').parents('tr'));

      // Add a class to identify the form 'New Vacancy Template'
      if ($('[name="entryURL"]').val().indexOf(';template=1') > -1) {
        $($('.CRM_HRRecruitment_Form_HRVacancy tbody').get(0)).addClass('CRM_HRRecruitment_Form_HRVacancy_Template');
      }
    }

    //change text from Client to Contact
    $('#crm-activity-view-table .crm-case-activity-view-Client .label').html('Contact');

    if (CRM.formName == 'contactForm' || CRM.pageName == 'viewSummary') {
      // Rename "Summary" tab to "Personal Details"
      // Hack to check contact type - This field only appears for individuals
      if ($('.crm-contact-job_title', '.crm-summary-contactinfo-block').length) {
        $('.crm-contact-tabs-list #tab_summary a', e.target).text('Personal Details');
      }

      addGovernmentIdField(e.target);
      miscPageChanges(e.target);
    }

    $('span.crm-frozen-field', '.crm-profile-name-hrident_tab').closest('div').parent('div').hide();

    //changes of sorce help text
    $('INPUT#contact_source').parent('td').children('a').click(function () {
      $('#crm-notification-container .crm-help .notify-content').remove();

      if ($('#crm-notification-container .crm-help p').length) {
      	$('#crm-notification-container .crm-help p').remove();
      }

      $('#crm-notification-container .crm-help').append('<p>Source is a useful field where data has been migrated to CiviHR from one or a number of other legacy systems. The Source field will indicate which legacy system the contact has come from.</p>');
    });
  });

  // Remove the arrow for menu items with sub-items, and replaces it
  // with a font awesome caret
  $(document).ready(function () {
    $('#root-menu-div .menu-item-arrow').each(function ($element) {
      var $arrow = $(this);

      $arrow.before('<i class="fa fa-caret-right menu-item-arrow"></i>');
      $arrow.remove();
    });
  });
}(CRM.$, CRM._));
