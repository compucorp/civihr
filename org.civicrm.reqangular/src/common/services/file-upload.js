define([
  'common/modules/services',
  'common/angularFileUpload'
], function (module) {
  'use strict';

  module.factory('FileUpload', ['$q', 'FileUploader',
    function ($q, FileUploader) {

      /**
      * Helper to call throw on expressions by wrapping it on function
      *
      * @param {String} error message to throw
      */
      function _throw (msg) {
        throw msg;
      }

      return {

        /**
         * Sets up the file uploader service. It throws error if required params not found.
         *
         * @param {Object} customSettings can have keys like url for file upload path,
         * formData for updating form data, queueLimit to limit number of files that can be uploaded at onCompleteAll
         *
         * @return {Object} an instance of FileUploader
         */
        uploader: function (customSettings) {
          if (!customSettings) {
            throw 'custom settings object need to be defined in paramter';
          }

          return new FileUploader({
            url: customSettings.url || '/civicrm/ajax/attachment',
            queueLimit: +customSettings.queueLimit || 1,
            formData: [{
              entity_table: customSettings.entityTable || _throw('entityTable missing from custom settings'),
              crm_attachment_token: customSettings.crmAttachmentToken || _throw('crmAttachmentToken missing from custom settings')
            }]
          });
        },

        /**
         * Uploads all files in queue updating with additional form data
         *
         * @param {Object} fileUploader instance of FileUploader that was created by the service
         * @param {Object} additionalFormData that has keys like entityID or description
         *
         * @return {Promise} that resolves to result if successful else error
         */
        uploadAll: function (fileUploader, additionalFormData) {
          var deferred = $q.defer(),
            results = [];

          /**
          * FileUploader callback that gets fired before each file item gets
          * uploaded, useful to insert additional form params like entityId
          * which are not available at the time of uploader creation.
          *
          * @param {Object} item file item being uploaded
          */
          fileUploader.onBeforeUploadItem = function (item) {
            for (key in additionalFormData) {
              var snakeCaseKey = _.snakeCase(key);
              item.formData.push({
                snakeCaseKey: additionalFormData[key]
              });
            }
          };

          /**
          * FileUploader callback that gets fired after each file item gets
          * uploaded, useful to obtain result of upload. The result of each
          * upload are aggregated in local results array.
          *
          * @param {Object} item file item being uploaded
          * @param {Object} response obtained from server for upload
          */
          fileUploader.onCompleteItem = function (item, response) {
            results.push(response);
          };

          /**
          * FileUploader callback to capture error during upload.
          *
          * @param {Object} item file item being uploaded
          * @param {Object} response obtained from server for upload
          * @param {Number} status
          * @param {Object} headers
          */
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

          /**
          * FileUploader callback that gets fired after uploading each file item
          * in a queue or a single file item.
          *
          */
          fileUploader.onCompleteAll = function () {
            deferred.resolve(results);
          };

          /**
          * FileUploader method to start uploading files.
          *
          */
          fileUploader.uploadAll();

          return deferred.promise;
        }
      };
    }
  ]);
});
