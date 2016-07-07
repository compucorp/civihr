(function ($, _) {
  $(document).on('crmLoad', function(e) {
    $('.crm-inline-edit').one('DOMSubtreeModified', function() {
      if ($(this).find('form').length === 1) {
        $(this).find('form label').each(function() {
          var id = $(this).attr('for');
          $('#' + id).attr('placeholder', $(this).text());
        });
      }
    });
  });
}(CRM.$, CRM._));
