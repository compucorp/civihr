define(['controllers/controllers'], function(controllers){
    controllers.controller('ModalDialogCtrl',['$scope','$modalInstance', '$timeout', 'content', '$log',
        function($scope, $modalInstance, $timeout, content, $log){
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
        }]);
});