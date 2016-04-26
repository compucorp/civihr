define([], function () {
    'use strict';

    return ['$scope','$uibModalInstance', 'content',
        function ($scope, $modalInstance, content) {
            $scope.title = content.title || 'CiviHR';
            $scope.msg = content.msg || '';
            $scope.copyConfirm = content.copyConfirm || 'Yes';
            $scope.copyCancel = content.copyCancel || 'Cancel';
            $scope.classConfirm = content.classConfirm || 'btn-primary';

            $scope.confirm = function (action) {
                $modalInstance.close(action || true);
            };

            $scope.cancel = function () {
                $modalInstance.close(false);
            };
        }
    ];
});
