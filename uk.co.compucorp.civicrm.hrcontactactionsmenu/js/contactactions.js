(function ($) {
  $(document)
    // Actions menu
    .on('click', function(e) {
      if ($(e.target).is('#crm-contact-actions-link, #crm-contact-actions-link *')) {
        $('#crm-contact-actions-link').addClass('active');
      } else {
        $('#crm-contact-actions-link').removeClass('active');
      }
    });
}(CRM.$));
