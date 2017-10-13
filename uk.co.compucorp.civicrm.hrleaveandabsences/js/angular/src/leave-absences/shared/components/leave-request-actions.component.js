/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/hr-settings',
  'common/services/notification.service',
  'common/services/pub-sub'
], function (_, moment, components) {
  components.component('leaveRequestActions', {
    bindings: {
      leaveRequest: '<',
      leaveRequestStatuses: '<',
      absenceTypes: '<',
      /**
       * Role is not a permission level in this case.
       * For example Manager can act as Staff
       * and Admin can act as either Manager or Staff.
       */
      role: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-actions.html';
    }],
    controllerAs: 'actions',
    controller: LeaveRequestActionsController
  });

  LeaveRequestActionsController.$inject = ['$log', '$rootScope', 'dialog',
    'LeavePopup', 'pubSub', 'shared-settings', 'notificationService'];

  function LeaveRequestActionsController ($log, $rootScope, dialog,
    LeavePopup, pubSub, sharedSettings, notification) {
    $log.debug('Component: leave-request-action-dropdown');

    var vm = this;
    var statusIdBeforeAction;
    var statusNames = sharedSettings.statusNames;
    var actions = {
      edit: {
        label: 'Edit',
        allowedStatuses: [statusNames.awaitingApproval]
      },
      respond: {
        label: 'Respond',
        allowedStatuses: [statusNames.moreInformationRequired]
      },
      view: {
        label: 'View',
        allowedStatuses: [
          statusNames.approved,
          statusNames.rejected,
          statusNames.cancelled
        ]
      },
      approve: {
        label: 'Approve',
        isDirectAction: true,
        allowedStatuses: [statusNames.awaitingApproval],
        dialog: {
          title: 'Approval',
          btnClass: 'success',
          btnLabel: 'Approve',
          msg: 'Please confirm approval'
        }
      },
      reject: {
        label: 'Reject',
        isDirectAction: true,
        allowedStatuses: [statusNames.awaitingApproval],
        dialog: {
          title: 'Rejection',
          btnClass: 'warning',
          btnLabel: 'Reject',
          msg: 'Please confirm rejection'
        }
      },
      cancel: {
        label: 'Cancel',
        isDirectAction: true,
        allowedStatuses: [
          statusNames.awaitingApproval,
          statusNames.approved,
          statusNames.rejected,
          statusNames.moreInformationRequired
        ],
        dialog: {
          title: 'Cancellation',
          btnClass: 'danger',
          btnLabel: 'Confirm',
          msg: 'Please confirm cancellation'
        }
      },
      delete: {
        label: 'Delete',
        isDirectAction: true,
        allowedStatuses: [
          statusNames.awaitingApproval,
          statusNames.moreInformationRequired,
          statusNames.approved,
          statusNames.rejected,
          statusNames.cancelled
        ],
        dialog: {
          title: 'Deletion',
          btnClass: 'danger',
          btnLabel: 'Confirm',
          msg: 'This cannot be undone'
        }
      }
    };

    vm.allowedActions = [];

    vm.action = action;
    vm.openLeavePopup = openLeavePopup;

    (function init () {
      indexSupportData();
      setAllowedActions();
    }());

    /**
     * Performs an action on a given leave request
     *
     * @param {string} action
     */
    function action (action) {
      var dialogParams = actions[action].dialog;

      statusIdBeforeAction = vm.leaveRequest.status_id;

      dialog.open({
        title: 'Confirm ' + dialogParams.title + '?',
        copyCancel: 'Cancel',
        copyConfirm: dialogParams.btnLabel,
        classConfirm: 'btn-' + dialogParams.btnClass,
        msg: dialogParams.msg,
        onConfirm: function () {
          return vm.leaveRequest[action]()
            .then(function () {
              publishEvents(action);
            })
            .catch(function (error) {
              notification.error('Error:', error);
            });
        }
      });
    }

    /**
     * Checks if the given leave request can be cancelled
     *
     * @TODO This function utilises external resource
     * vm.absenceTypes - this sould be refactored
     *
     * @return {Boolean}
     */
    function canLeaveRequestBeCancelled (leaveRequestStatus) {
      var allowCancellationValue = vm.absenceTypes[vm.leaveRequest.type_id].allow_request_cancelation;

      // Admin can always cancel
      if (vm.role === 'admin') {
        return true;
      }

      // Others but Admin can cancel if only the leave request status is
      // either "Awaiting for Approval" or "More Information Required"
      if (!_.includes([statusNames.awaitingApproval, statusNames.moreInformationRequired],
        leaveRequestStatus)) {
        return false;
      }

      // Manager can cancel disregarding the allow_request_cancelation value
      if (vm.role === 'manager') {
        return true;
      }

      // If request can only be cancelled in advance of start date
      if (allowCancellationValue === '3') {
        return moment().isBefore(vm.leaveRequest.from_date);
      }

      // If request can always be cancelled
      return allowCancellationValue === '2';
    }

    /**
     * Indexes leave request statuses and absence types
     * if they are passed as arrays to the component
     */
    function indexSupportData () {
      if (_.isArray(vm.leaveRequestStatuses)) {
        vm.leaveRequestStatuses = _.indexBy(vm.leaveRequestStatuses, 'value');
      }

      if (_.isArray(vm.absenceTypes)) {
        vm.absenceTypes = _.indexBy(vm.absenceTypes, 'id');
      }
    }

    /**
     * Opens the leave request popup
     *
     * When the component appears inside other elements
     * which also having click events, event.stopPropagation() is necessary
     * to prevent the click events of parent elements from being called
     *
     * @param {Object} event
     * @param {Object} leaveRequest
     * @param {String} leaveType
     * @param {String} selectedContactId
     * @param {Boolean} isSelfRecord
     */
    function openLeavePopup (event, leaveRequest, leaveType, selectedContactId, isSelfRecord) {
      event.stopPropagation();
      LeavePopup.openModal(leaveRequest, leaveType, selectedContactId, isSelfRecord);
    }

    /**
     * Publish events
     *
     * @param {String} action
     */
    function publishEvents (action) {
      var awaitingApprovalStatusValue = _.find(vm.leaveRequestStatuses, function (status) {
        return status.name === sharedSettings.statusNames.awaitingApproval;
      }).value;

      // Check if the status was "Awaiting Approval" before the action
      if (statusIdBeforeAction === awaitingApprovalStatusValue) {
        pubSub.publish('ManagerBadge:: Update Count');
      }

      $rootScope.$emit('LeaveRequest::' + (action === 'delete' ? 'deleted' : 'edit'),
        vm.leaveRequest);
    }

    /**
     * @TODO This function utilises external resources:
     * vm.leaveRequestStatuses - this sould be refactored
     *
     * Sets actions that can be performed within the
     * leave request basing on its status and user role
     *
     */
    function setAllowedActions () {
      var leaveRequestStatus = vm.leaveRequestStatuses[vm.leaveRequest.status_id].name;
      var allowedActions = _.compact(_.map(actions, function (action, actionKey) {
        return _.includes(action.allowedStatuses, leaveRequestStatus) ? actionKey : null;
      }));

      (!canLeaveRequestBeCancelled(leaveRequestStatus)) && _.pull(allowedActions, 'cancel');
      (vm.role !== 'admin') && _.pull(allowedActions, 'delete');
      (vm.role === 'staff') && _.pull(allowedActions, 'approve', 'reject');
      (vm.role !== 'staff') && swapViewEditAndRespondActions(allowedActions);

      vm.allowedActions = _.map(allowedActions, function (action) {
        return {
          key: action,
          label: actions[action].label,
          isDirectAction: actions[action].isDirectAction
        };
      });
    }

    /**
     * Swaps Edit and Respond actions in allowed actions list
     *
     * @param {Array} actions
     */
    function swapViewEditAndRespondActions (actions) {
      _.each(actions, function (action, actionKey) {
        (action === 'edit') && (actions[actionKey] = 'respond');
        (_.includes(['respond', 'view'], action)) && (actions[actionKey] = 'edit');
      });
    }
  }
});
