/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/hr-settings',
  'common/models/session.model'
], function (_, moment, components) {
  components.component('leaveRequestPopupCommentsTab', {
    bindings: {
      canManage: '<',
      mode: '<',
      request: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup-comments-tab.html';
    }],
    controllerAs: 'commentsCtrl',
    controller: ['$log', '$rootScope', 'HR_settings', 'shared-settings', 'Contact', 'Session', controller]
  });

  function controller ($log, $rootScope, HRSettings, sharedSettings, Contact, Session) {
    $log.debug('Component: leave-request-popup-comments-tab');

    var loggedInContactId = null;
    var vm = this;

    vm.loading = { component: true };
    vm.comment = {
      text: '',
      contacts: {}
    };

    (function init () {
      loadCommentsAndContactNames();
      loadLoggedInContactId();
    }());

    /**
     * Add a comment into comments array, also clears the comments textbox
     */
    vm.addComment = function () {
      vm.request.comments.push({
        contact_id: loggedInContactId,
        created_at: moment(new Date()).format(sharedSettings.serverDateTimeFormat),
        leave_request_id: vm.request.id,
        text: vm.comment.text
      });
      vm.comment.text = '';
    };

    /**
     * Format a date-time into user format and returns
     *
     * @return {String}
     */
    vm.formatDateTime = function (dateTime) {
      return moment.utc(dateTime, sharedSettings.serverDateTimeFormat).local().format(HRSettings.DATE_FORMAT.toUpperCase() + ' HH:mm');
    };

    /**
     * Returns the comments which are not marked for deletion
     *
     * @return {Array}
     */
    vm.getActiveComments = function () {
      return vm.request.comments.filter(function (comment) {
        return !comment.toBeDeleted;
      });
    };

    /**
     * Returns the comment author name
     * @param {String} contactId
     *
     * @return {String}
     */
    vm.getCommentorName = function (contactId) {
      if (contactId === loggedInContactId) {
        return 'Me';
      } else if (vm.comment.contacts[contactId]) {
        return vm.comment.contacts[contactId].display_name;
      }
    };

    /**
     * Checks if popup is opened in given mode
     *
     * @param {String} modeParam to open leave request like edit or view or create
     * @return {Boolean}
     */
    vm.isMode = function (modeParam) {
      return vm.mode === modeParam;
    };

    /**
     * Orders comment, used as a angular filter
     * @param {Object} comment
     *
     * @return {Date}
     */
    vm.orderComment = function (comment) {
      return moment(comment.created_at, sharedSettings.serverDateTimeFormat);
    };

    /**
     * Decides visiblity of remove comment button
     * @param {Object} comment - comment object
     *
     * @return {Boolean}
     */
    vm.removeCommentVisibility = function (comment) {
      return !comment.comment_id || vm.canManage;
    };

    /**
     * Loads unique contact names for all the comments
     *
     * @return {Promise}
     */
    function loadContactNames () {
      var contactsIndex = _.indexBy(vm.request.comments, 'contact_id');
      var contactIDs = Object.keys(contactsIndex);

      return Contact.all({
        id: { IN: contactIDs }
      }, { page: 1, size: 0 })
        .then(function (contacts) {
          vm.comment.contacts = _.indexBy(contacts.list, 'contact_id');
        });
    }

    /**
     * Loads the comments for current leave request
     *
     * @return {Promise}
     */
    function loadCommentsAndContactNames () {
      return vm.request.loadComments()
        .then(function () {
          $rootScope.$broadcast('LeaveRequestPopup::requestObjectUpdated');
          // loadComments sets the comments on request object instead of returning it
          vm.request.comments.length && loadContactNames();
        });
    }

    /**
     * Loads the contact id of the currently logged in user.
     *
     * @return {Promise}
     */
    function loadLoggedInContactId () {
      vm.loading.component = true;

      return Session.get().then(function (value) {
        loggedInContactId = value.contactId;
      })
      .then(function () {
        vm.loading.component = false;
      });
    }
  }
});
