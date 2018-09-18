/* eslint-env amd */

define(function () {
  'use strict';

  ModalProgressController.$inject = [
    '$log', '$q', '$scope', '$timeout', '$uibModalInstance', 'uploader',
    'promiseFilesUpload'
  ];

  function ModalProgressController ($log, $q, $scope, $timeout, $modalInstance, uploader,
    promiseFilesUpload) {
    $log.debug('Controller: ModalProgressController');

    var entityName, fieldName;

    $scope.uploader = uploader;

    $scope.cancel = cancel;

    (function init () {
      for (entityName in uploader) {
        for (fieldName in uploader[entityName]) {
          if (uploader[entityName][fieldName].queue.length) {
            uploader[entityName][fieldName].item = uploader[entityName][fieldName].queue[0].file.name;
          }
          uploader[entityName][fieldName].onProgressItem = function (item) {
            this.item = item.file.name;
          };
        }
      }

      $q.all(promiseFilesUpload).then(function (results) {
        $timeout(function () {
          $modalInstance.close(results);
        }, 500);
      });
    }());

    function cancel () {
      $modalInstance.dismiss('File upload canceled');
    }
  }

  return { ModalProgressController: ModalProgressController };
});
