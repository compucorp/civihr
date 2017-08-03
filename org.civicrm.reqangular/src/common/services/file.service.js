/* globals URL */
/* eslint-env amd */

define([
  'common/modules/services'
], function (services) {
  'use strict';

  services.factory('fileService', ['$http', '$window', '$log', function ($http, $window, $log) {
    $log.debug('Service: fileService');

    return {

      /**
       * Gets the file from server, creates its blob and opens it in new browser tab.
       *
       * @param {object} file
       * @return {promise}
       */
      openFile: function (file) {
        return $http.get(file.url, {responseType: 'arraybuffer'})
          .success(function (data) {
            var fileBlob = new Blob([data], { type: file.fileType });

            $window.open(URL.createObjectURL(fileBlob), '_blank');
          });
      }
    };
  }]);
});
