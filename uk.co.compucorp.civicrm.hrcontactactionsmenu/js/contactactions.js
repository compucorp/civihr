(function (CRM) {
  var $ = CRM.$;

  (function init () {
    initListeners();
  }());

  /**
   * Contains event listeners
   */
  function initListeners () {
    // Toogle Actions Menu's 'active' class
    $(document).on('click', function (e) {
      toggleActionMenuButtonStatus(e);
    });

    // Handler for deleting user account
    $('[data-delete-user-url]').on('click', function () {
      confirmUserAccountDeletion(this);
    });
  }

  /**
   * Displays confirmation before deleting user account
   * @param {Object} buttonInstance
   */
  function confirmUserAccountDeletion (buttonInstance) {
    var url = $(buttonInstance).attr('data-delete-user-url');

    CRM.confirm({
      'title': 'Confirmation dialog',
      'message': 'Are you sure you want to continue?'
    })
    .on('crmConfirm:yes', function () {
      window.location = url;
    });
  }

  /**
   * Toggles the "active" class in Actions Menu button
   * @param {Object} event
   */
  function toggleActionMenuButtonStatus (event) {
    var selector = $('#crm-contact-actions-link');

    if ($(event.target).is('#crm-contact-actions-link, #crm-contact-actions-link *')) {
      selector.addClass('active');
    } else {
      selector.removeClass('active');
    }
  }
}(CRM));
