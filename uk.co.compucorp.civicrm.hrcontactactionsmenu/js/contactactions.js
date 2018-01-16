(function ($, CRM) {
  $(document)
    // Actions menu
    .on('click', function (e) {
      if ($(e.target).is('#crm-contact-actions-link, #crm-contact-actions-link *')) {
        $('#crm-contact-actions-link').addClass('active');
      } else {
        $('#crm-contact-actions-link').removeClass('active');
      }
    });

  // Handler for deleting user account
  $('[data-delete-user-url]').on('click', function () {
    var url = $(this).attr('data-delete-user-url');

    CRM.confirm({
      'title': 'Confirmation dialog',
      'message': 'Are you sure you want to continue?'
    })
    .on('crmConfirm:yes', function () {
      window.location = url;
    });
  });
}(CRM.$, CRM));
