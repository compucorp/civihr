(function (CRM) {
  var $ = CRM.$;

  (function init () {
    initListeners();
  }());

  /**
   * Displays confirmation dialog before deleting user account
   */
  function confirmUserAccountDeletion () {
    var url = $('[data-delete-user-url]').attr('data-delete-user-url');

    CRM.confirm({
      'title': 'Confirmation dialog',
      'message': 'Are you sure you want to continue?'
    })
    .on('crmConfirm:yes', function () {
      window.location = url;
    });
  }

  /**
   * Initializes click listeners for
   * - Actions Button
   * - Delete User Account Button
   */
  function initListeners () {
    $(document).on('click', function (e) {
      toggleActionMenuButtonClass(e);
    });

    $('[data-delete-user-url]').on('click', function () {
      confirmUserAccountDeletion();
    });
  }

  /**
   * Toggles the "active" class in Actions Menu button
   *
   * @param {Object} event
   */
  function toggleActionMenuButtonClass (event) {
    var selector = $('#crm-contact-actions-link');

    if ($(event.target).is('#crm-contact-actions-link, #crm-contact-actions-link *')) {
      selector.addClass('active');
    } else {
      selector.removeClass('active');
    }
  }
}(CRM));
