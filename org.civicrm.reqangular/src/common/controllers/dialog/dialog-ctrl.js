define([], function () {
  'use strict';

  return ['$q', '$scope','$uibModalInstance', 'content', 'onConfirm',
    function ($q, $scope, $modalInstance, content, onConfirm) {
      $scope.loading = false

      $scope.title = content.title || 'CiviHR';
      $scope.msg = content.msg || '';
      $scope.copyConfirm = content.copyConfirm || 'Yes';
      $scope.copyCancel = content.copyCancel || 'Cancel';
      $scope.classConfirm = content.classConfirm || 'btn-primary';

      $scope.confirm = function () {
        $scope.loading = true;

        $q
          .resolve(onConfirm ? onConfirm() : null)
          .then(function () {
            $modalInstance.close(true);
          });
      };

      $scope.cancel = function () {
        $modalInstance.close(false);
      };
    }
  ];
});
