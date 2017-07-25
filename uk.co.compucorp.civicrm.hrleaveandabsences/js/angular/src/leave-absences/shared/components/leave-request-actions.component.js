/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
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
    controller: ['$log', '$q', '$rootScope', 'OptionGroup', 'dialog', 'shared-settings', controller]
  });

  function controller ($log, $q, $rootScope, OptionGroup, dialog, sharedSettings) {
    $log.debug('Component: leave-request-action-dropdown');

    var vm = this;
    var actions = {
      edit: {
        label: 'Edit',
        statuses: [sharedSettings.statusNames.awaitingApproval]
      },
      respond: {
        label: 'Respond',
        statuses: [sharedSettings.statusNames.moreInformationRequired]
      },
      view: {
        label: 'View',
        statuses: [
          sharedSettings.statusNames.approved,
          sharedSettings.statusNames.rejected,
          sharedSettings.statusNames.cancelled
        ]
      },
      approve: {
        label: 'Approve',
        isDirectAction: true,
        statuses: [sharedSettings.statusNames.awaitingApproval],
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
        statuses: [sharedSettings.statusNames.awaitingApproval],
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
        statuses: [
          sharedSettings.statusNames.awaitingApproval,
          sharedSettings.statusNames.moreInformationRequired
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
        statuses: [
          sharedSettings.statusNames.awaitingApproval,
          sharedSettings.statusNames.moreInformationRequired,
          sharedSettings.statusNames.approved,
          sharedSettings.statusNames.rejected,
          sharedSettings.statusNames.cancelled
        ],
        dialog: {
          title: 'Deletion',
          btnClass: 'danger',
          btnLabel: 'Confirm',
          msg: 'This cannot be undone'
        }
      }
    };

    vm.list = [];

    /**
     * Performs an action on a given leave request
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {string} action
     */
    vm.action = function (action) {
      var dialogParams = actions[action].dialog;

      dialog.open({
        title: 'Confirm ' + dialogParams.title + '?',
        copyCancel: 'Cancel',
        copyConfirm: dialogParams.btnLabel,
        classConfirm: 'btn-' + dialogParams.btnClass,
        msg: dialogParams.msg,
        onConfirm: function () {
          return vm.leaveRequest[action]()
            .then(function () {
              $rootScope.$emit('LeaveRequest::' + (action === 'delete' ? 'deleted' : 'edit'), vm.leaveRequest);
            });
        }
      });
    };

    init();

    function init () {
      /* This component expects vm.leaveRequestStatuses and vm.absenceTypes
       * to be an objects, so if arrays are passed, they are transformed into
       * object with according indexes
       */
      if (Array.isArray(vm.leaveRequestStatuses)) {
        vm.leaveRequestStatuses = _.indexBy(vm.leaveRequestStatuses, 'value');
      }

      if (Array.isArray(vm.absenceTypes)) {
        vm.absenceTypes = _.indexBy(vm.absenceTypes, 'id');
      }

      var allowedActions = getAllowedActions();

      vm.list = _.map(allowedActions, function (action) {
        return {
          key: action,
          label: actions[action].label,
          isDirectAction: actions[action].isDirectAction
        };
      });
    }

    /**
     * @TODO This function utilises external resource
     * vm.absenceTypes - this sould be refactored
     *
     * Checks if the given leave request can be cancelled
     *
     * Allow request cancellation values refer to the following constants:
     * 1 = REQUEST_CANCELATION_NO
     * 2 = REQUEST_CANCELATION_ALWAYS
     * 3 = REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE
     *
     * @return {Boolean}
     */
    function canLeaveRequestBeCancelled () {
      if (vm.role === 'admin' || vm.role === 'manager') {
        return true;
      }

      var allowCancellationValue = vm.absenceTypes[vm.leaveRequest.type_id].allow_request_cancelation;

      if (allowCancellationValue === '3') {
        return moment().isBefore(vm.leaveRequest.from_date);
      }

      return allowCancellationValue === '2';
    }

    /**
     * @TODO This function utilises external resource
     * vm.leaveRequestStatuses - this sould be refactored
     *
     * Defines which actions can be taken with the
     * leave request basing on its status, user role
     *
     * @return {Array} allowed actions
     */
    function getAllowedActions () {
      var leaveRequestStatus = vm.leaveRequestStatuses[vm.leaveRequest.status_id].name;
      var allowedActions = [];

      _.each(actions, function (action, actionKey) {
        if (_.include(action.statuses, leaveRequestStatus)) {
          allowedActions.push(actionKey);
        }
      });

      if (!canLeaveRequestBeCancelled()) {
        _.pull(allowedActions, 'cancel');
      }

      if (vm.role !== 'admin') {
        _.pull(allowedActions, 'delete');
      }

      if (vm.role === 'staff') {
        _.pull(allowedActions, 'approve', 'reject');
      } else {
        // Inverts Edit and Respond actions for Admin and Manager
        allowedActions = _.map(allowedActions, function (action) {
          if (action === 'edit') { return 'respond'; }
          if (action === 'respond') { return 'edit'; }
          return action;
        });
      }

      return allowedActions;
    }
  }
});
