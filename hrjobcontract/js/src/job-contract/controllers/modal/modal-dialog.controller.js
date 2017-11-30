/* eslint-env amd */

define(function () {
  'use strict';

  ModalDialogCtrl.__name = 'ModalDialogCtrl';
  ModalDialogCtrl.$inject = [
    '$scope', '$uibModalInstance', '$timeout', 'content', '$log'
  ];

  function ModalDialogCtrl ($scope, $modalInstance, $timeout, content, $log) {
    $log.debug('Controller: ModalDialogCtrl');

    $scope.title = content.title || 'CiviHR Job Contract';
    $scope.msg = content.msg || '';
    $scope.copyConfirm = content.copyConfirm || 'Yes';
    $scope.copyCancel = content.copyCancel || 'Cancel';

    $scope.confirm = function (action) {
      $modalInstance.close(action || true);
    };

    $scope.cancel = function () {
      $modalInstance.dismiss('Cancel');
    };
  }

  return ModalDialogCtrl;
});
