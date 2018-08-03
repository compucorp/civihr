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
      return sharedSettings.sharedPathTpl + 'components/leave-request-popup/leave-request-popup-comments-tab.html';
    }],
    controllerAs: 'commentsCtrl',
    controller: ['$log', '$rootScope', '$scope', 'HR_settings', 'shared-settings', 'Contact', 'Session', controller]
  });

  function controller ($log, $rootScope, $scope, HRSettings, sharedSettings, Contact, Session) {
    $log.debug('Component: leave-request-popup-comments-tab');

    var loggedInContactId = null;
    var vm = this;

    vm.loading = { component: true };
    vm.comment = {
      text: '',
      contacts: {}
    };

    vm.$onInit = $onInit;
    vm.addComment = addComment;
    vm.canRemoveComment = canRemoveComment;
    vm.canSubmit = canSubmit;
    vm.formatDateTime = formatDateTime;
    vm.getActiveComments = getActiveComments;
    vm.getCommentorName = getCommentorName;
    vm.isMode = isMode;
    vm.onBeforeSubmit = onBeforeSubmit;

    function $onInit () {
      $scope.$emit('LeaveRequestPopup::addTab', vm);
      loadCommentsAndContactNames();
      loadLoggedInContactId();
    }

    /**
     * Adds a comment into comments array and also clears the comments textbox
     */
    function addComment () {
      vm.request.comments.push({
        contact_id: loggedInContactId,
        leave_request_id: vm.request.id,
        text: vm.comment.text
      });
      vm.comment.text = '';
    }

    /**
     * Allows the user to submit the request if there is a comment waiting to be
     * added.
     *
     * @return {Boolean}
     */
    function canSubmit () {
      return vm.comment.text.length > 0;
    }

    /**
     * Determines if a comment can be deleted when the comment has not been saved
     * or if the user can manage the request and has permission to remove the
     * comments.
     *
     * @param {Object} comment - comment object
     * @return {Boolean}
     */
    function canRemoveComment (comment) {
      return !comment.comment_id || vm.canManage;
    }

    /**
     * Format a date-time into user format and returns
     *
     * @return {String}
     */
    function formatDateTime (dateTime) {
      return moment
        .utc(dateTime, sharedSettings.serverDateTimeFormat).local()
        .format(HRSettings.DATE_FORMAT.toUpperCase() + ' HH:mm');
    }

    /**
     * Returns the comments which are not marked for deletion
     *
     * @return {Array}
     */
    function getActiveComments () {
      return vm.request.comments.filter(function (comment) {
        return !comment.toBeDeleted;
      });
    }

    /**
     * Returns the comment author name
     * @param {String} contactId
     *
     * @return {String}
     */
    function getCommentorName (contactId) {
      if (contactId === loggedInContactId) {
        return 'Me';
      } else if (vm.comment.contacts[contactId]) {
        return vm.comment.contacts[contactId].display_name;
      }
    }

    /**
     * Checks if popup is opened in given mode
     *
     * @param {String} mode to open leave request like edit or view or create
     * @return {Boolean}
     */
    function isMode (mode) {
      return vm.mode === mode;
    }

    /**
     * When submitting the tab, if there are comments waiting to be added, it
     * automatically adds them to the request.
     */
    function onBeforeSubmit () {
      (vm.comment.text.length) && vm.addComment();
    }

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

      return Session.get()
        .then(function (session) {
          loggedInContactId = session.contactId;
        })
        .then(function () {
          vm.loading.component = false;
        });
    }
  }
});
