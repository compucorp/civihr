/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (_, moment, components) {
  components.component('leaveRequestPopupFilesTab', {
    bindings: {
      // canManage: '<',
      mode: '<',
      request: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup-files-tab.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', 'HR_settings', 'shared-settings', controller]
  });

  function controller ($log, HRSettings, sharedSettings) {
    $log.debug('Component: leave-request-popup-files-tab');

    var vm = Object.create(this);
    vm.supportedFileTypes = '';
    vm.today = Date.now();
    vm.userDateFormatWithTime = HRSettings.DATE_FORMAT + ' HH:mm';
    vm.userDateFormat = HRSettings.DATE_FORMAT;

    (function init () {
      vm.supportedFileTypes = _.keys(sharedSettings.fileUploader.allowedMimeTypes);
      vm.request.loadAttachments();
    }());

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

      return vm.request.files.length + vm.request.fileUploader.queue.length - filesWithSoftDelete.length;
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

    return vm;
  }
});
