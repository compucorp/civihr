(function ($, _) {
  $(document).on('crmLoad', function(e) {
    $('.crm-address-block').one('DOMSubtreeModified', function () {
      if ($(this).find('form').length === 1) {
        $(this).find('form .form-layout-compressed label').each(function() {
          var id = $(this).attr('for');
          $('#' + id).attr('placeholder', $(this).text());
          $(this).remove();
        });
      }
    });
  });
}(CRM.$, CRM._));
