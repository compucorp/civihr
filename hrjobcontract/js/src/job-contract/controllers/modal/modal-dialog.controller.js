/* eslint-env amd */

define(function () {
  'use strict';

  ModalDialogController.$inject = [
    '$log', '$scope', '$timeout', '$uibModalInstance', 'content'
  ];

  function ModalDialogController ($log, $scope, $timeout, $modalInstance, content) {
    $log.debug('Controller: ModalDialogController');

    $scope.copyCancel = content.copyCancel || 'Cancel';
    $scope.copyConfirm = content.copyConfirm || 'Yes';
    $scope.msg = content.msg || '';
    $scope.title = content.title || 'CiviHR Job Contract';

    $scope.cancel = cancel;
    $scope.confirm = confirm;

    function cancel () {
      $modalInstance.dismiss('Cancel');
    }

    function confirm (action) {
      $modalInstance.close(action || true);
    }
  }

  return { ModalDialogController: ModalDialogController };
});
