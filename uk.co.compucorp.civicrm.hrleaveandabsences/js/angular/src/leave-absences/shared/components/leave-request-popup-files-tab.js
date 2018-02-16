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
      mode: '<',
      request: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-popup/leave-request-popup-files-tab.html';
    }],
    controllerAs: 'filesTab',
    controller: ['$log', '$q', '$rootScope', '$scope', 'HR_settings',
      'shared-settings', 'OptionGroup', 'FileUpload', 'fileMimeTypes', controller]
  });

  function controller ($log, $q, $rootScope, $scope, HRSettings, sharedSettings,
    OptionGroup, FileUpload, fileMimeTypes) {
    $log.debug('Component: leave-request-popup-files-tab');

    var fileExtensions = [];
    var listeners = [];
    var mimeTypes = {};
    var vm = Object.create(this);

    vm.filesLoaded = false;
    vm.fileUploader = null;
    vm.today = Date.now();
    vm.userDateFormatWithTime = HRSettings.DATE_FORMAT + ' HH:mm';
    vm.userDateFormat = HRSettings.DATE_FORMAT;

    vm.$onDestroy = unsubscribeFromEvents;
    vm.canRemoveAttachment = canRemoveAttachment;
    vm.canSubmit = canSubmit;
    vm.canUploadMore = canUploadMore;
    vm.formatDateTime = formatDateTime;
    vm.getAuthorName = getAuthorName;
    vm.getFilesAmount = getFilesAmount;
    vm.listFileTypes = listFileTypes;

    (function init () {
      $rootScope.$broadcast('LeaveRequestPopup::childComponent::register');
      $scope.$emit('LeaveRequestPopup::addTab', vm);
      initListeners();

      $q.all([
        loadSupportedFileExtensions(),
        loadAttachments()
      ])
        .then(initFileUploader)
        .finally(function () {
          vm.filesLoaded = true;
        });
    }());

    /**
     * Allows the user to submit the request if files are waiting to be uploaded.
     *
     * @return {Boolean}
     */
    function canSubmit () {
      return vm.fileUploader && vm.fileUploader.queue.length > 0;
    }

    /**
     * Checks if the total of already uploaded and queued files does not exceed
     * the allowed limit
     *
     * @return {Boolean} true is user can upload more else false
     */
    function canUploadMore () {
      return vm.getFilesAmount() < sharedSettings.fileUploader.queueLimit;
    }

    /**
     * Format a date-time into user format and returns
     *
     * @return {String}
     */
    function formatDateTime (dateTime) {
      return moment(dateTime, sharedSettings.serverDateTimeFormat)
        .format(vm.userDateFormat.toUpperCase() + ' HH:mm');
    }

    /**
     * Returns the attachment author name
     *
     * @param {String} contactId
     * @return {String}
     */
    function getAuthorName (contactId) {
      // @TODO Author name cannot be fetched for already uploaded attachments
      // as the attachment API does not support saving the contact id
      return (contactId === vm.request.contact_id ? 'Me -' : '');
    }

    /**
     * Calculates the total number of files associated with request.
     *
     * @return {Number} of files
     */
    function getFilesAmount () {
      var filesToDelete = getFilesMarkedForDeletion();
      var queue = getFileUploaderQueue();

      return vm.request.files.length + queue.length - filesToDelete.length;
    }

    /**
     * Returns an array of files marked for deletion
     *
     * @return {Array}
     */
    function getFilesMarkedForDeletion () {
      return _.filter(vm.request.files, function (file) {
        return file.toBeDeleted;
      });
    }

    /**
     * Returns the file uploader queue
     *
     * @return {Array}
     */
    function getFileUploaderQueue () {
      return (vm.fileUploader && vm.fileUploader.queue)
        ? vm.fileUploader.queue
        : [];
    }

    /**
     * Initializes all the listeners
     */
    function initListeners () {
      listeners.push(
        $rootScope.$on('LeaveRequestPopup::submit', uploadAttachments)
      );
    }

    /**
     * Initializes the file uploader object
     */
    function initFileUploader () {
      loadMimeTypesOfSupportedFileExtensions()
        .then(function () {
          vm.fileUploader = FileUpload.uploader({
            entityTable: 'civicrm_hrleaveandabsences_leave_request',
            crmAttachmentToken: sharedSettings.attachmentToken,
            queueLimit: sharedSettings.fileUploader.queueLimit,
            allowedMimeTypes: mimeTypes
          });
        });
    }

    /**
     * Returns a string of allowed files extensions for upload
     * @returns {string}
     */
    function listFileTypes () {
      return fileExtensions.length > 0
        ? fileExtensions.map(function (ext) {
          return ext.label;
        }).join(', ')
        : '';
    }

    /**
     * Loads the attachments, and broadcasts an event when they are loaded
     */
    function loadAttachments () {
      return vm.request.loadAttachments()
        .then(function () {
          $rootScope.$broadcast('LeaveRequestPopup::requestObjectUpdated');
        });
    }

    /**
     * Loads the mime types for supported file extensions
     *
     * @returns {Promise}
     */
    function loadMimeTypesOfSupportedFileExtensions () {
      return $q.all(fileExtensions.map(function (fileExtension) {
        return fileMimeTypes.getMimeTypeFor(fileExtension.label)
          .then(function (mimeType) {
            mimeTypes[fileExtension.label] = mimeType;
          });
      }))
        .catch(function () {
          // if the API calls throws an error or fails, "allowedMimeTypes" will be undefined
          // hence the default file extension will be set to the uploader in file-upload.js
          mimeTypes = null;
        });
    }

    /**
     * Load file extensions which are supported for upload and creates uploader object
     *
     * @return {Promise}
     */
    function loadSupportedFileExtensions () {
      return OptionGroup.valuesOf('safe_file_extension')
        .then(function (fileExtensionsData) {
          fileExtensions = fileExtensionsData;
        });
    }

    /**
     * Returns true if the attachment can be removed because it has not yet been
     * saved or because the user can manage the request.
     *
     * @param {Object} attachment - attachment object
     * @return {Boolean}
     */
    function canRemoveAttachment (attachment) {
      return !attachment.attachment_id || vm.canManage;
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
     * * Upload attachment in file uploder's queue
     * @param {Object} e - event object
     * @param {Function} callBack - call back function
     */
    function uploadAttachments (e, callBack) {
      if (vm.fileUploader.queue && vm.fileUploader.queue.length > 0) {
        vm.fileUploader.uploadAll({ entityID: vm.request.id })
          .then(function () { callBack(); })
          .catch(callBack);
      } else {
        callBack();
      }
    }

    return vm;
  }
});
