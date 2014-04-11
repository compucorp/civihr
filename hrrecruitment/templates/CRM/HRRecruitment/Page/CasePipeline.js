(function($, _) {
  function loadDetails(context) {
    var url,
      $checked = $('.select-row:checked', context),
      $detail = $('.hr-pipeline-case-details', context);
    if ($checked.length === 1) {
      url = CRM.url('civicrm/case/hrapplicantprofile', $.extend({reset: 1}, $checked.closest('tr').data()));
      CRM.loadPage(url, {target: $detail});
    }
    else {
      $detail.data('civiCrmSnippet') && $detail.crmSnippet('destroy');
      // Todo: comparison view
      $detail.html('<p class="hr-applicant-selection-msg">' + ts('%1 applicants selected', {1: $checked.length}) + '</p>');
    }
    // Enable/disable actions
    $('.hr-pipeline-case-actions', context).css('opacity', $checked.length ? '' : '.5');
  }

  function createActivity(url, args, context) {
    var params,
      $checked = $('.select-row:checked', context);
    if ($checked.length) {
      params = {
        reset: 1,
        action: 'add',
        caseid: $checked.map(function() {return this.value;}).get().join()
      };
      return CRM.loadForm(CRM.url(url, $.extend(params, args)))
        .on('crmFormSuccess', function() {
          CRM.tabHeader.resetTab(CRM.tabHeader.getActiveTab());
        });
    }
  }

  $(function() {
    $('#crm-main-content-wrapper')
      // Don't visit the contact link
      .on('click', 'a.hr-pipeline-contact-link', function(e) {
        e.preventDefault();
      })
      // Check the box when clicking anywhere in the row
      .on('click', '.hr-pipeline-case-contacts tr', function(e) {
        if (!$(e.target).is(':checkbox')) {
          var $checkBox = $(this).find('input.select-row');
          $checkBox.prop('checked', !$checkBox.prop('checked')).trigger('change');
        }
      })
      // When changing selection
      .on('change', '.hr-pipeline-case-contacts input:checkbox', function(e, data) {
        // Ignore the extra events triggered by master checkbox
        if (data !== 'master-selected') {
          loadDetails($(this).closest('.hr-pipeline-tab'));
        }
      })
      .on('click', '.hr-activity-button', function(e) {
        e.preventDefault();
        createActivity($(this).attr('href').split('#')[1], $(this).data(), $(this).closest('.hr-pipeline-tab'));
      })
      .on('change', '.hr-activity-menu', function() {
        createActivity("civicrm/case/activity", {atype: $(this).val()}, $(this).closest('.hr-pipeline-tab'));
        $(this).select2('val', '');
      })
      .on('change', '.hr-case-status-menu', function() {
        var context = $(this).closest('.hr-pipeline-tab'),
          statusId =  $(this).val(),
          form = createActivity("civicrm/case/activity", {atype: $(this).data('atype'), case_status_id: statusId}, context);
        $(this).select2('val', '');
        if (form) {
          var $checked = $('.select-row:checked', context);
          form.on('crmFormSuccess', function () {
            var tab = 'li.crm-tab-button[data-status_id=' + statusId + ']';
            CRM.tabHeader.updateCount(tab, CRM.tabHeader.getCount(tab) + $checked.length);
            CRM.tabHeader.resetTab(tab);
          });
        }
      });
  });
}(CRM.$, _));
