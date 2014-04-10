(function($, _) {
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
          var context = $(this).closest('.hr-pipeline-tab'),
            $checked = $('.select-row:checked', context),
            $detail = $('.hr-pipeline-case-details', context);
          if ($checked.length === 1) {
            var url = CRM.url('civicrm/case/hrapplicantprofile', $.extend({reset: 1}, $checked.closest('tr').data()));
            CRM.loadPage(url, {target: $detail});
          }
          else {
            $detail.data('civiCrmSnippet') && $detail.crmSnippet('destroy');
            // Todo: comparison view
            $detail.html('<div class="hr-applicant-selection-msg">' + ts('%1 applicants selected', {1: $checked.length}) + '</div>');
          }
        }
      });
  });
}(cj, _));
