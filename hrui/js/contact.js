(function ($, _) {
  $(document).on('crmLoad', function(e) {
    $('.crm-inline-edit').one('DOMSubtreeModified', function() {
      var $form = $(this).find('form');

      if ($form.length === 1) {
        $form.find('label').each(function() {
          var $label = $(this);
          var id = $label.attr('for');
          $('#' + id).attr('placeholder', $label.text());
        });
      }
    });
  });
}(CRM.$, CRM._));
