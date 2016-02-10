define([
    'common/modules/dialog'
], function (dialog) {
    'use strict';

    dialog.controller('DialogCtrl', ['$scope','$modalInstance', 'content',
        function ($scope, $modalInstance, content) {
            $scope.title = content.title || 'CiviHR';
            $scope.msg = content.msg || '';
            $scope.copyConfirm = content.copyConfirm || 'Yes';
            $scope.copyCancel = content.copyCancel || 'Cancel';

            $scope.confirm = function (action) {
                $modalInstance.close(action || true);
            };

            $scope.cancel = function () {
                $modalInstance.close(false);
            };
        }
    ]);
});
