(function($, _) {
  $(function() {
    $('#crm-main-content-wrapper').on('click', 'a.hr-pipeline-case-link', function(e) {
      var context = $(this).closest('.hr-pipeline-tab');
      $('.hr-pipeline-case-details', context).crmSnippet({url: $(this).attr('href')}).crmSnippet('refresh');
      e.preventDefault();
    });
  });
}(cj, _));
