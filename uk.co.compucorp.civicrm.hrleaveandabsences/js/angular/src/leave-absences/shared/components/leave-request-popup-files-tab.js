/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/file-upload',
  'common/services/file-mime-types',
  'common/services/hr-settings'
], function (_, moment, components) {
  components.component('leaveRequestPopupFilesTab', {
    bindings: {
      canManage: '<',
      fileUploader: '=',
      mode: '<',
      request: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup-files-tab.html';
    }],
    controllerAs: 'filesTab',
    controller: ['$log', '$rootScope', 'HR_settings', 'shared-settings', 'OptionGroup', 'FileUpload', 'FileMimeTypes', controller]
  });

  function controller ($log, $rootScope, HRSettings, sharedSettings, OptionGroup, FileUpload, FileMimeTypes) {
    $log.debug('Component: leave-request-popup-files-tab');

    var vm = Object.create(this);
    var events = [];
    vm.supportedFileTypes = '';
    vm.today = Date.now();
    vm.userDateFormatWithTime = HRSettings.DATE_FORMAT + ' HH:mm';
    vm.userDateFormat = HRSettings.DATE_FORMAT;

    /**
     * Checks if user can upload more file, it totals the number of already
     * uploaded files and those which are in queue and compares it to limit.
     *
     * @return {Boolean} true is user can upload more else false
     */
    vm.canUploadMore = function () {
      return vm.getFilesCount() < sharedSettings.fileUploader.queueLimit;
    };

    /**
     * Format a date-time into user format and returns
     *
     * @return {String}
     */
    vm.formatDateTime = function (dateTime) {
      return moment(dateTime, sharedSettings.serverDateTimeFormat).format(vm.userDateFormat.toUpperCase() + ' HH:mm');
    };

    /**
     * Returns the attachment author name
     * @param {String} contactId
     *
     * @return {String}
     */
    vm.getAuthorName = function (contactId) {
      // @TODO Author name cannot be fetched for already uploaded attachments
      // as the attachment API does not support saving the contact id
      if (contactId === vm.request.contact_id) {
        return 'Me -';
      }
    };

    /**
     * Calculates the total number of files associated with request.
     *
     * @return {Number} of files
     */
    vm.getFilesCount = function () {
      var filesWithSoftDelete = _.filter(vm.request.files, function (file) {
        return file.toBeDeleted;
      });
      var queueLength = (vm.fileUploader && vm.fileUploader.queue)
        ? vm.fileUploader.queue.length : 0;

      return vm.request.files.length + queueLength - filesWithSoftDelete.length;
    };

    /**
     * Decides visibility of remove attachment button
     * @param {Object} attachment - attachment object
     *
     * @return {Boolean}
     */
    vm.removeAttachmentVisibility = function (attachment) {
      return !attachment.attachment_id || vm.canManage;
    };

    /**
     * Gets called when the component is destroyed
     */
    vm.$onDestroy = function () {
      // destroy all the event
      events.map(function (event) {
        event();
      });
    };

    (function init () {
      loadSupportedFileTypes();
      events.push($rootScope.$on('uploadFiles: start', uploadAttachments));
    }());

    /**
     * Load file extensions which are supported for upload and creates uploader object
     *
     * @return {Promise}
     */
    function loadSupportedFileTypes () {
      var allowedMimeTypes;

      return OptionGroup.valuesOf('safe_file_extension')
        .then(function (extensions) {
          allowedMimeTypes = {};

          vm.supportedFileTypes = extensions.map(function (ext) {
            allowedMimeTypes[ext.label] = FileMimeTypes.getMimeTypeFor(ext.label);
            return ext.label;
          });
        })
        .finally(function () {
          // if the API calls throws an error or fails, "allowedMimeTypes" will be undefined
          // hence the default extension will be set to the uploader in file-upload.js
          vm.fileUploader = FileUpload.uploader({
            entityTable: 'civicrm_hrleaveandabsences_leave_request',
            crmAttachmentToken: sharedSettings.attachmentToken,
            queueLimit: sharedSettings.fileUploader.queueLimit,
            allowedMimeTypes: allowedMimeTypes
          });
        });
    }

    /**
     * Upload attachment in file uploder's queue, fires an event when done
     */
    function uploadAttachments () {
      if (vm.fileUploader.queue && vm.fileUploader.queue.length > 0) {
        vm.fileUploader.uploadAll({ entityID: vm.request.id })
        .then(function () {
          $rootScope.$broadcast('uploadFiles: success');
        })
        .catch(function () {
          $rootScope.$broadcast('uploadFiles: error');
        });
      } else {
        $rootScope.$broadcast('uploadFiles: success');
      }
    }

    return vm;
  }
});
