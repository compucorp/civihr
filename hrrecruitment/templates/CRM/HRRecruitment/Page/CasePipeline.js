// https://civicrm.org/licensing
(function($, _) {
  function loadDetails(context) {
    var url,
      $checked = $('.select-row:checked', context),
      $detail = $('.hr-pipeline-case-details', context),
      $eval = '';
    if ($checked.length === 1) {
      url = CRM.url('civicrm/case/hrapplicantprofile', $.extend({reset: 1}, $checked.closest('tr').data()));
      CRM.loadPage(url, {target: $detail});
      $eval = 1;
    }
    else {
      $detail.data('civiCrmSnippet') && $detail.crmSnippet('destroy');
      // Todo: comparison view
      $detail.html('<p class="hr-applicant-selection-msg">' + ts('%1 applicants selected', {1: $checked.length}) + '</p>');
    }

    // Enable/disable actions
    $('.hr-pipeline-case-actions', context).css('opacity', $checked.length ? '' : '.5');
    $('.hr-pipeline-case-actions .hr-eval-button', context).css('opacity', $eval ? '' : '.5');
  }

  function createActivity(url, args, context) {
    var params,
      $checked = $('.select-row:checked', context),
      $checkBox = $checked.map(function() {return $(this).closest('tr').attr('data-cid');});

    if ($checked.length) {
      params = {
        reset: 1,
        action: 'add',
        caseid: $checked.map(function() {return this.value;}).get().join(),
        cid: $checked.map(function() {return $(this).closest('tr').attr('data-cid');}).get().join(),
      };
      return CRM.loadForm(CRM.url(url, $.extend(params, args)))
        .on('crmFormSuccess', function() {
          $.each($checkBox, function(val, i){
            var ele = $('tr[data-cid='+i+']');
            $('tr[data-cid='+i+']').closest('a.hr-pipeline-contact-link').trigger('click');
            loadDetails($('tr[data-cid='+i+'] input:checkbox').closest('.hr-pipeline-tab'));
          });
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
      // For Evaluation action
      .on('click', '.hr-eval-button', function(e) {
        e.preventDefault();
        var $context = $(this).closest('.hr-pipeline-tab'),
          $checked = $('.select-row:checked', $context);
        if ($checked.length == 1) {
	  var jsonObj = '{"atype" : "'+$(this).data('atype')+'"',
            data;
	  jsonObj += ',"id" : "'+$('.hr-case-application-evaluation-url', $context).attr('data-id')+'"';
          jsonObj += ',"action" : "'+$('.hr-case-application-evaluation-url', $context).attr('data-action')+'"}';
          data = $.parseJSON(jsonObj);
          createActivity($(this).attr('href').split('#')[1], data, $(this).closest('.hr-pipeline-tab'));
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
            CRM.tabHeader.resetTab(CRM.tabHeader.getActiveTab());
            CRM.tabHeader.updateCount(tab, CRM.tabHeader.getCount(tab) + $checked.length);
            CRM.tabHeader.resetTab(tab);
          });
        }
      });
  });
}(CRM.$, _));
