/* eslint-env amd */

define([
  'job-roles/modules/job-roles.controllers'
], function (controllers) {
  'use strict';

  controllers.controller('ModalDialogCtrl', ModalDialogCtrl);

  ModalDialogCtrl.$inject = ['$scope', '$uibModalInstance', '$timeout', 'content', '$log'];

  function ModalDialogCtrl ($scope, $modalInstance, $timeout, content, $log) {
    $log.debug('Controller: ModalDialogCtrl');

    $scope.title = content.title || 'CiviHR Job Roles';
    $scope.msg = content.msg || '';
    $scope.copyConfirm = content.copyConfirm || 'Yes';
    $scope.copyCancel = content.copyCancel || 'Cancel';

    $scope.confirm = confirm;
    $scope.cancel = cancel;

    /**
     * Confirms the dialog
     *
     * @param  {boolean} action
     */
    function confirm (action) {
      $modalInstance.close(action || true);
    }

    /**
     * Cancels the dialog
     */
    function cancel () {
      $modalInstance.dismiss('Cancel');
    }
  }
});
