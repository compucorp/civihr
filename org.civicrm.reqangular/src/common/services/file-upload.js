define([
  'common/modules/services',
], function (module) {
  'use strict';

  module.factory('FileUploadService', ['$q', 'FileUploader',
    function ($q, FileUploader) {
      var fileUploader;

      return {

        /**
         * sets up the file uploader service
         *
         * @param {Object} customSettings can have keys like url for file upload path,
         * formData for updating form data, queueLimit to limit number of files that can be uploaded at onCompleteAll
         *
         * @return {Object} an instance of FileUploader
         **/
        uploader: function (customSettings) {
          var uploaderSettings = {};

          uploaderSettings.url = 'url' in customSettings ? customSettings.url : '/civicrm/ajax/attachment';
          uploaderSettings.formData = [{
            entityTable: customSettings.entityTable
          }];

          if (customSettings.queueLimit && typeof customSettings.queueLimit === 'number') {
            uploaderSettings.queueLimit = customSettings.queueLimit;
          }

          fileUploader = new FileUploader(uploaderSettings);
          return fileUploader;
        },

        /**
         * uploads all files in queue updating with additional form data
         *
         * @param {Object} additionalFormData that has keys like entityID
         **/
        uploadAll: function (additionalFormData) {
          var deferred = $q.defer(),
            results = [];

          fileUploader.onBeforeUploadItem = function (item) {
            for (key in additionalFormData) {
              item.formData.push({
                key: additionalFormData[key]
              });
            }
          };

          fileUploader.onCompleteItem = function (item, response) {
            results.push(response);
          };

          fileUploader.onErrorItem = function (item, response, status, headers) {
            deferred.reject('Could not upload file: ' + item.file.name);
            $log.error(' ===== Item Error: ' + status + ' ======');
            $log.error(' =====  - item ======');
            $log.error(item);
            $log.error(' =====  - response ======');
            $log.error(response);
            $log.error(' =====  - headers ======');
            $log.error(headers);
          };

          fileUploader.onCompleteAll = function () {
            deferred.resolve(results);
          };

          fileUploader.uploadAll();

          return deferred.promise;
        }
      };
    }
  ]);
});
