/* globals URL */
/* eslint-env amd */

define([
  'common/modules/services'
], function (services) {
  'use strict';

  services.factory('fileService', ['$http', '$window', '$q', '$log', function ($http, $window, $q, $log) {
    $log.debug('Service: fileService');

    return {

      /**
       * Gets the file from server, creates its blob and opens it in new browser tab.
       * @param  {object} file
       * @return {object}
       */
      openFile: function (file) {
        var deferred = $q.defer();

        $http.get(file.url, {responseType: 'arraybuffer'})
          .success(function (data) {
            var fileBlob = new Blob([data], { type: file.fileType });

            $window.open(URL.createObjectURL(fileBlob), '_blank');
            deferred.resolve(fileBlob);
          });

        return deferred.promise;
      }
    };
  }]);
});
