/* eslint-env amd */

(function ($) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    $(document).ready(function () {
      bootstrapContactAccessRightsAppOnReadyEvent();
      markMenuButtonAsActiveWhenPressed();
      showConfirmationDialogWhenDeletingUserAccount();
    });

    /**
     * Listens to the "ready" event once the resources for the
     * contact access rights app have been loaded, and then bootstraps it
     */
    function bootstrapContactAccessRightsAppOnReadyEvent () {
      document.addEventListener('accessRightsReady', function () {
        angular.bootstrap('[data-contact-access-rights]', ['access-rights']);
      });
    }

    /**
     * Toggles the "active" class in "Actions" button
     *
     * @param {Object} event
     */
    function markMenuButtonAsActiveWhenPressed (event) {
      $(document).on('click', function (event) {
        var $button = $('#crm-contact-actions-link');
        var $target = $(event.target);

        if ($target.is($button) || $button.has($target).length) {
          $button.addClass('active');
        } else {
          $button.removeClass('active');
        }
      });
    }

    /**
     * Displays confirmation dialog before deleting user account
     */
    function showConfirmationDialogWhenDeletingUserAccount () {
      $('[data-delete-user-url]').on('click', function () {
        var url = $(this).attr('data-delete-user-url');

        CRM
          .confirm({
            'title': 'Confirm',
            'message': 'Are you sure you want to delete the user account?'
          })
          .on('crmConfirm:yes', function () {
            window.location = url;
          });
      });
    }
  });
}(CRM.$));
