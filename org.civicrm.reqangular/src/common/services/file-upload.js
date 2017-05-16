define([
  'common/modules/services',
  'common/lodash',
  'common/angular-file-upload'
], function (module, _) {
  'use strict';

  module.factory('FileUpload', ['$q', '$log', 'FileUploader',
    function ($q, $log, FileUploader) {

      /**
       * Helper to call throw on expressions by wrapping it on function
       *
       * @param {String} error message to throw
       */
      function error(param) {
        throw param + ' missing from parameter';
      }

      /**
       * FileUploader callback to capture error calls this helper to log error.
       *
       * @param {Object} item file item being uploaded
       * @param {Object} response obtained from server for upload
       * @param {Number} status
       * @param {Object} headers
       */
      function logError(item, response, status, headers) {
        $log.error(' ===== Item Error: ' + status + ' ======');
        $log.error(' =====  - item ======');
        $log.error(item);
        $log.error(' =====  - response ======');
        $log.error(response);
        $log.error(' =====  - headers ======');
        $log.error(headers);
      }

      /**
       * Helper to set defaults if not set in passed parameter
       *
       * @param {Object} customSettings user defined settings for uploader
       * @return {Object} a updated list of settings
       */
      function setDefaults(customSettings) {

        return _.defaults(customSettings, {
          allowedMimeTypes: ['plain', 'png', 'jpeg', 'bmp', 'gif', 'pdf'],
          url: '/civicrm/ajax/attachment',
          queueLimit: 1
        });
      }

      return {

        /**
         * Sets up the file uploader service. It throws error if required params not found.
         *
         * @param {Object} customSettings can have keys like url for file upload path,
         * formData for updating form data, queueLimit to limit number of files that can be uploaded at onCompleteAll
         * @return {Object} an instance of FileUploader
         * @throws {String} of error if parameters are not set properly
         */
        uploader: function (customSettings) {
          var uploader, oldUploadAll, deferred = $q.defer(),
            results = [];

          if (!customSettings) {
            return error('custom settings');
          }

          customSettings = setDefaults(customSettings);

          uploader = new FileUploader({
            url: customSettings.url,
            queueLimit: +customSettings.queueLimit,
            onCompleteItem: function (item, response) { results.push(response); },
            onCompleteAll: function () { deferred.resolve(results); },
            onErrorItem: function (item) {
              logError.apply(null, arguments);
              deferred.reject('Could not upload file: ' + item.file.name);
            },
            formData: [{
              entity_table: customSettings.entityTable || error('entityTable'),
              crm_attachment_token: customSettings.crmAttachmentToken || error('crmAttachmentToken')
            }],
            filters: [{
              name: 'fileFormatFilter',
              fn: function (item) {
                var mimeType = item.type.slice(item.type.lastIndexOf('/') + 1);

                return _.includes(customSettings.allowedMimeTypes, mimeType);
              }
            }]
          });

          /**
           * Uploads all files in queue updating with additional form data.
           * Wraps the default `uploadAll` in a custom method so we can
           * return a promise
           *
           * @param {Object} additionalFormData that has keys like entityID or description
           * @return {Promise} that resolves to result if successful else error
           */
          uploader.uploadAll = (function () {
            oldUploadAll = uploader.uploadAll;

            return function (additionalFormData) {
              /**
               * FileUploader callback that gets fired before each file item gets
               * uploaded, useful to insert additional form params like entityId
               * which are not available at the time of uploader creation.
               *
               * @param {Object} item file item being uploaded
               */
              uploader.onBeforeUploadItem = function (item) {
                _.each(additionalFormData, function (val, key) {
                  var modifiedItem = {};
                  modifiedItem[_.snakeCase(key)] = val;
                  item.formData.push(modifiedItem);
                });
              };

              oldUploadAll.apply(uploader);

              return deferred.promise;
            }
          }());

          return uploader;
        }
      };
    }
  ]);
});
