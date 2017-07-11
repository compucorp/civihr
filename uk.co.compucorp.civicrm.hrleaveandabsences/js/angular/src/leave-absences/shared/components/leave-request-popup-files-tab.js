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
    controller: ['$log', 'HR_settings', 'shared-settings', 'Contact', controller]
  });

  function controller ($log, HRSettings, sharedSettings, Contact) {
    $log.debug('Component: leave-request-popup-files-tab');

    var filesContacts = [];
    var vm = Object.create(this);
    vm.supportedFileTypes = '';
    vm.today = Date.now();
    vm.userDateFormatWithTime = HRSettings.DATE_FORMAT + ' HH:mm';
    vm.userDateFormat = HRSettings.DATE_FORMAT;

    (function init () {
      vm.supportedFileTypes = _.keys(sharedSettings.fileUploader.allowedMimeTypes);
      vm.request.loadAttachments()
      .then(function () {
        loadContactNames();
      });
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
      if (contactId === vm.request.contact_id) {
        return 'Me';
      } else if (filesContacts[contactId]) {
        return filesContacts[contactId].display_name;
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

    /**
     * Loads unique contact names for all the comments
     *
     * @return {Promise}
     */
    function loadContactNames () {
      var contactIDs = [];

      _.each(vm.request.files, function (file) {
        // Push only unique contactId's which are not same as logged in user
        if (file.contact_id !== vm.request.contact_id && contactIDs.indexOf(file.contact_id) === -1) {
          contactIDs.push(file.contact_id);
        }
      });

      return Contact.all({
        id: { IN: contactIDs }
      }, { page: 1, size: 0 })
        .then(function (contacts) {
          filesContacts = _.indexBy(contacts.list, 'contact_id');
        });
    }

    return vm;
  }
});
