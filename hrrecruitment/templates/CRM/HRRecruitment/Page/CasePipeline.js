(function($, _) {
  $(function() {
    $('#crm-main-content-wrapper').on('click', 'a.hr-pipeline-case-link', function(e) {
      $('#activities-selector').remove();
      var context = $(this).closest('.hr-pipeline-tab');
      $('.hr-pipeline-case-details', context).crmSnippet({url: $(this).attr('href')}).crmSnippet('refresh');
      e.preventDefault();
    });

    $("#mainTabContainer").on('tabsactivate', function(e, ui) {
      $('#activities-selector').remove();
      var activeAria = $(this).find('.ui-tabs-active').attr("aria-controls");
      if ($('#'+activeAria).find('input:hidden[name=entryURL]').val()) {
        var link = $('#'+activeAria).find('input:hidden[name=entryURL]').val().replace(new RegExp('&amp;', 'g'), '&');
        $('#'+activeAria).find('.hr-pipeline-case-details').crmSnippet({url: link}).crmSnippet('refresh');
      }
    });
  });
}(cj, _));
