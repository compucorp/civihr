/* global Ps */

// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  $(document)
    .on('crmLoad', function (e) {
      addUploadFileListener("input[type='file']");
      amendVacancyForm();
      amendContactPageAndForm(e);
      applyMiscChanges();
      changeContactSourceFieldHelpText();
    })
    .ready(function () {
      addUserMenuToMainMenu();
      amendAppLogoMenuItem();
      amendApplicationForm();
      useFontAwesomeArrowsInSubMenuItems();
      toggleActiveClassOnHoverOnAnyMainMenuItem();
    });

  /**
   * Customizes the app logo menu item, swithing from the CiviCRM logo
   * to the CiviHR logo, and making the item a direct link instead of a
   * toggle for a sub menu
   */
  function amendAppLogoMenuItem () {
    var $menuItem = $('.crm-link-home');
    var $wrappedLogo = swapAndWrapAppLogo($menuItem);
    var $customHomeLink = customizeHomeLinkInLogoMenuItem($menuItem, $wrappedLogo);

    removeLogoSubMenuAndKeepOnlyHomeLink($menuItem, $customHomeLink);
  }

  /**
   * Adds the user menu by fetching it from the hrcore extension
   */
  function addUserMenuToMainMenu () {
    var wrapperId = 'civihr-menu';

    if (!$('#' + wrapperId).length) {
      $.ajax('/civicrm/hrcore/usermenu?snippet=4', {
        dataType: 'html',
        success: function (menuMarkup) {
          injectUserMenuInAMainMenuWrapper(menuMarkup, wrapperId);
        }
      });
    }
  }

  /**
   * Adds the Government ID field on the Personal Details page and on the Edit
   * Contact form.
   */
  function addGovernmentIdField () {
    // Updates Personal Details Page
    if (CRM.cid && CRM.hideGId) {
      var inlineDataBlock = $('.Inline_Custom_Data');

      inlineDataBlock.appendTo($('#contactinfo-block').parent('div').parent('div'));
      inlineDataBlock.removeClass('crm-collapsible');
      inlineDataBlock.removeClass('collapsed');
      inlineDataBlock.addClass('crm-summary-block');

      $('.Inline_Custom_Data div.collapsible-title').css({display: 'none'});
      $('.Inline_Custom_Data div.crm-summary-block').css({display: 'block'});
    }

    // Updates Edit Contact Form
    if ($('#customFields').length < 1) {
      $('#Inline_Custom_Data label').each(function () {
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
   * Add an event listener on input[type="file"]
   * @param {jQuery Object} selector [selector from input file]
   */
  function addUploadFileListener (selector) {
    if ($(selector).length === 1) {
      $(selector).on('change', insertFile);
    }
  }

  /**
   * Amends the application form
   */
  function amendApplicationForm () {
    $('.CRM_HRRecruitment_Form_Application').addClass('crm-form-block');
    $('.CRM_HRRecruitment_Form_Application .crm-profile-name-application_profile').addClass('form-layout-compressed');
  }

  /**
   * Amends the contact page and the contact form
   */
  function amendContactPageAndForm (e) {
    if (CRM.formName === 'contactForm' || CRM.pageName === 'viewSummary') {
      // Rename "Summary" tab to "Personal Details"
      // Hack to check contact type - This field only appears for individuals
      if ($('.crm-contact-job_title', '.crm-summary-contactinfo-block').length) {
        $('.crm-contact-tabs-list #tab_summary a', e.target).text('Personal Details');
      }

      addGovernmentIdField(e.target);
      miscContactPageChanges(e.target);
    }
  }

  /**
   * Amends the vacancy form
   */
  function amendVacancyForm () {
    if ($('.CRM_HRRecruitment_Form_HRVacancy').length === 1) {
      linkLabelToDatepickerInput($('label[for="start_date"]').parents('tr'));
      linkLabelToDatepickerInput($('label[for="end_date"]').parents('tr'));

      // Add a class to identify the form 'New Vacancy Template'
      if ($('[name="entryURL"]').val().indexOf(';template=1') > -1) {
        $($('.CRM_HRRecruitment_Form_HRVacancy tbody').get(0)).addClass('CRM_HRRecruitment_Form_HRVacancy_Template');
      }
    }
  }

  /**
   * Applies miscellaneous UI changes
   */
  function applyMiscChanges () {
    $('#activityCustomData').attr('colspan', 3);
    $('#crm-activity-view-table .crm-case-activity-view-Client .label').html('Contact');
    $('span.crm-frozen-field', '.crm-profile-name-hrident_tab').closest('div').parent('div').hide();

    $('.crm-accordion-header.crm-master-accordion-header').on('click', function () {
      window.setTimeout(function () {
        Array.prototype.forEach.call(document.querySelectorAll('.listing-box'), function (element) {
          Ps.initialize(element);
        });
      }, 0);
    });
  }

  /**
   * Changes of sorce help text
   */
  function changeContactSourceFieldHelpText () {
    $('INPUT#contact_source').parent('td').children('a').click(function () {
      $('#crm-notification-container .crm-help .notify-content').remove();

      if ($('#crm-notification-container .crm-help p').length) {
        $('#crm-notification-container .crm-help p').remove();
      }

      $('#crm-notification-container .crm-help').append('<p>Source is a useful field where data has been migrated to CiviHR from one or a number of other legacy systems. The Source field will indicate which legacy system the contact has come from.</p>');
    });
  }

  /**
   * Finds the original link to the homepage, changes the text, wraps it in a
   * `menumain-label` element and prepends internally the given app logo
   *
   * @param {object} $menuItem The context where to find the link
   * @param {object} $appLogo
   * @return the customized home link
   */
  function customizeHomeLinkInLogoMenuItem ($menuItem, $appLogo) {
    var $homeLink = $('li > a', $menuItem).first();

    return $homeLink
      .text('Home')
      .wrapInner('<span class="menumain-label">')
      .prepend($appLogo);
  }

  /**
   * Injects the given markup in a menu wrapper with the given id
   * created to contain both the original menu and the user one
   *
   * @param {string} menuMarkup
   * @param {string} wrapperId
   */
  function injectUserMenuInAMainMenuWrapper (menuMarkup, wrapperId) {
    var $menuMarkup = $(menuMarkup);
    var $menuWrapper = $('<div>');

    $menuWrapper.attr('id', wrapperId);
    $menuWrapper.append($('#civicrm-menu'));
    $menuWrapper.append($menuMarkup);
    $menuWrapper.insertAfter('#page');
  }

  /**
   * Insert a DOM node after input[type="file"]
   * with the filename
   */
  function insertFile () {
    var fileName = $(this)[0].files[0];

    $('#js-uploaded-file').remove();
    if (fileName !== undefined) {
      $(this).after('<span id="js-uploaded-file" class="uploaded-file">' + fileName.name + ' <span class="uploaded-file-icon-trash"><i class="fa fa-trash-o"></i> Remove</span>');

      $('.uploaded-file-icon-trash').on('click', removeFile);
    }
  }

  /**
   * Update label 'for' attr to works with the datepicker
   *
   * @param  {jQuery object} $line [datepicker's line parent]
   */
  function linkLabelToDatepickerInput ($line) {
    $line.find('label').attr('for', $line.find('.crm-form-date').attr('id'));
  }

  /**
   * Misc changes to the page (hiding elements, inserting new ones, etc)
   */
  function miscContactPageChanges (target) {
    // Hide current employer and job title
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

    // shift demographic above extended demographic
    $('.crm-demographics-accordion', 'form#Contact').insertAfter($('.crm-contactDetails-accordion'));

    if ($('tr#Phone_Block_2', 'form#Contact').length < 1) {
      $('#addPhone').click();
    }
  }

  /**
   * Remove the #js-uploaded-file DIV and
   * clean input[type="file"] value
   */
  function removeFile () {
    var $input = $('#js-uploaded-file').parent().find('input[type="file"]');

    $('#js-uploaded-file').remove();
    $input.val('');
  }

  /**
   * Moves the given home link right under the menu item and gets rid
   * of the original sub menu
   *
   * @param {object} $menuItem The context where to find the link
   * @param {object} $homeLink
   */
  function removeLogoSubMenuAndKeepOnlyHomeLink ($menuItem, $homeLink) {
    $menuItem
      .off() // removes any handler that the original item had
      .find('#civicrm-home')
      .before($homeLink)
      .remove();
  }

  /**
   * Swaps the CiviCRM logo with the CiviHR logo
   * and wraps it in a `menumain-icon` element
   *
   * @param {object} $menuItem The context where to find the logo
   * @return the wrapper of the logo
   */
  function swapAndWrapAppLogo ($menuItem) {
    var $appLogo = $('.crm-logo-sm', $menuItem);

    return $appLogo
      .addClass('chr_logo chr_logo--default-color')
      .removeClass('crm-logo-sm')
      .wrap('<span class="menumain-icon">')
      .parent();
  }

  /**
   * CiviCRM by default applies on hover the .activetarget class
   * only to main menu items with a submenu
   *
   * This functions makes sure that any item gets the class applied,
   * even those with just a direct link
   */
  function toggleActiveClassOnHoverOnAnyMainMenuItem () {
    var className = 'activetarget';

    $('.menumain').not('.crm-Self_Service_Portal').hover(function () {
      $(this).addClass(className);
    }, function () {
      $(this).removeClass(className);
    });
  }

  /**
   * Remove the arrow for menu items with sub-items, and replaces it
   * with a font awesome caret
   */
  function useFontAwesomeArrowsInSubMenuItems () {
    $('#root-menu-div .menu-item-arrow').each(function ($element) {
      var $arrow = $(this);

      $arrow.before('<i class="fa fa-caret-right menu-item-arrow"></i>');
      $arrow.remove();
    });
  }
}(CRM.$, CRM._));
