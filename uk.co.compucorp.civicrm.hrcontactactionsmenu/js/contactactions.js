(function (CRM) {
  var $ = CRM.$;

  (function init () {
    initListeners();
  }());

  /**
   * Displays confirmation dialog before deleting user account
   */
  function confirmUserAccountDeletion () {
    $('[data-delete-user-url]').on('click', function () {
      var url = $(this).attr('data-delete-user-url');

      CRM
        .confirm({
          'title': 'Confirmation dialog',
          'message': 'Are you sure you want to continue?'
        })
        .on('crmConfirm:yes', function () {
          window.location = url;
        });
    });
  }

  /**
   * Initializes click listeners for
   * - Delete User Account Button
   * - Actions Button
   */
  function initListeners () {
    confirmUserAccountDeletion();
    toggleActionMenuButtonClass();
  }

  /**
   * Toggles the "active" class in "Actions" button
   *
   * @param {Object} event
   */
  function toggleActionMenuButtonClass (event) {
    $(document).on('click', function (event) {
      var selector = $('#crm-contact-actions-link');

      if ($(event.target).is('#crm-contact-actions-link, #crm-contact-actions-link *')) {
        selector.addClass('active');
      } else {
        selector.removeClass('active');
      }
    });
  }
}(CRM));
