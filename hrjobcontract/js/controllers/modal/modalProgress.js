define(['controllers/controllers'], function(controllers){
    controllers.controller('ModalProgressCtrl',['$scope','$modalInstance', '$q', '$timeout', 'uploader',
        'promiseFilesUpload', '$log',
        function($scope, $modalInstance, $q, $timeout, uploader, promiseFilesUpload, $log){
            $log.debug('Controller: ModalProgressCtrl');

            var entityName, fieldName;

            $scope.uploader = uploader;

            for (entityName in uploader) {
                for (fieldName in uploader[entityName]){
                    if (uploader[entityName][fieldName].queue.length) {
                        uploader[entityName][fieldName].item = uploader[entityName][fieldName].queue[0].file.name;
                    }
                    uploader[entityName][fieldName].onProgressItem = function(item){
                        this.item = item.file.name;
                    };
                }
            }

            $q.all(promiseFilesUpload).then(function(results){
                $timeout(function(){
                    $modalInstance.close(results);
                },500);
            });

            $scope.cancel = function () {
                $modalInstance.dismiss('File upload canceled');
            };

        }]);
});