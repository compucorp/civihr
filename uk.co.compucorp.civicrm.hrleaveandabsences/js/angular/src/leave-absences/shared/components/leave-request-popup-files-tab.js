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
    controller: ['$log', '$rootScope', '$q', 'HR_settings', 'shared-settings', 'OptionGroup', 'FileUpload', 'fileMimeTypes', controller]
  });

  function controller ($log, $rootScope, $q, HRSettings, sharedSettings, OptionGroup, FileUpload, fileMimeTypes) {
    $log.debug('Component: leave-request-popup-files-tab');

    var allowedMimeTypes;
    var listeners = [];
    var vm = Object.create(this);
    vm.filesLoaded = false;
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
      var filesToDelete = filesMarkedForDeletion();
      var queue = fileUploaderQueue();

      return vm.request.files.length + queue.length - filesToDelete.length;
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

    vm.$onDestroy = unsubscribeFromEvents;

    (function init () {
      $rootScope.$broadcast('LeaveRequestModal::childProcess::register');
      initListeners();

      $q.all([
        loadSupportedFileTypes(),
        vm.request.loadAttachments()
      ])
      .then(function () {
        $rootScope.$broadcast('LeaveRequestModal::requestObjectUpdated');
      })
      .finally(function () {
        vm.filesLoaded = true;
      });
    }());

    /**
     * Returns an array of files marked for deletion
     *
     * @return {Array}
     */
    function filesMarkedForDeletion () {
      return _.filter(vm.request.files, function (file) {
        return file.toBeDeleted;
      });
    }

    /**
     * Returns the file uploader queue
     *
     * @return {Array}
     */
    function fileUploaderQueue () {
      return (vm.fileUploader && vm.fileUploader.queue)
        ? vm.fileUploader.queue : [];
    }

    /**
     * Initializes all the listeners
     */
    function initListeners () {
      listeners.push($rootScope.$on('LeaveRequestModal::childProcess::start', uploadAttachments));
    }

    /**
     * Initializes the file uploader object
     */
    function initFileUploader () {
      // if the API calls throws an error or fails, "allowedMimeTypes" will be undefined
      // hence the default file extension will be set to the uploader in file-upload.js
      vm.fileUploader = FileUpload.uploader({
        entityTable: 'civicrm_hrleaveandabsences_leave_request',
        crmAttachmentToken: sharedSettings.attachmentToken,
        queueLimit: sharedSettings.fileUploader.queueLimit,
        allowedMimeTypes: allowedMimeTypes
      });
    }

    /**
     * Load file extensions which are supported for upload and creates uploader object
     *
     * @return {Promise}
     */
    function loadSupportedFileTypes () {
      return OptionGroup.valuesOf('safe_file_extension')
        .then(function (fileExtensions) {
          var fileExtensionPromise = [];

          vm.supportedFileTypes = fileExtensions.map(function (ext) {
            return ext.label;
          });

          // set mime types
          allowedMimeTypes = {};
          _.forEach(fileExtensions, function (ext) {
            fileExtensionPromise.push(fileMimeTypes.getMimeTypeFor(ext.label)
            .then(function (mimeType) {
              allowedMimeTypes[ext.label] = mimeType;
            }));
          });

          return fileExtensionPromise;
        })
        .finally(initFileUploader);
    }

    /**
     * Gets called when the component is destroyed
     */
    function unsubscribeFromEvents () {
      // destroy all the event
      _.forEach(listeners, function (listener) {
        listener();
      });
    }

    /**
     * Upload attachment in file uploder's queue, fires an event when done
     */
    function uploadAttachments () {
      if (vm.fileUploader.queue && vm.fileUploader.queue.length > 0) {
        vm.fileUploader.uploadAll({ entityID: vm.request.id })
        .then(function () {
          $rootScope.$broadcast('LeaveRequestModal::childProcess::success');
        })
        .catch(function () {
          $rootScope.$broadcast('LeaveRequestModal::childProcess::error', 'File Upload Error');
        });
      } else {
        $rootScope.$broadcast('LeaveRequestModal::childProcess::success');
      }
    }

    return vm;
  }
});
