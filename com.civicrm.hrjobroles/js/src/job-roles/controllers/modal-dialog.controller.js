/* eslint-env amd */

define(function () {
  'use strict';

  ModalDialogController.__name = 'ModalDialogController';
  ModalDialogController.$inject = ['$uibModalInstance', '$log', '$timeout', 'content'];

  function ModalDialogController ($modalInstance, $log, $timeout, content) {
    $log.debug('Controller: ModalDialogController');

    var vm = this;

    vm.copyCancel = content.copyCancel || 'Cancel';
    vm.copyConfirm = content.copyConfirm || 'Yes';
    vm.msg = content.msg || '';
    vm.title = content.title || 'CiviHR Job Roles';

    vm.cancel = cancel;
    vm.confirm = confirm;

    /**
     * Cancels the dialog
     */
    function cancel () {
      $modalInstance.dismiss('Cancel');
    }

    /**
     * Confirms the dialog
     *
     * @param  {boolean} action
     */
    function confirm (action) {
      $modalInstance.close(action || true);
    }
  }

  return ModalDialogController;
});
