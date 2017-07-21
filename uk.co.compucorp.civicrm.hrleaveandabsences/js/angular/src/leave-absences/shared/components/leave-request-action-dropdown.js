/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (_, moment, components) {
  components.component('leaveRequestActionDropdown', {
    bindings: {
      request: '<',
      requestStatuses: '<',
      absenceTypes: '<',
      /**
       * Role is not a permission level in this case.
       * For example Manager can act as Staff
       * and Admin can act as either Manager or Staff.
       */
      role: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-action-dropdown.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', '$q', '$rootScope', 'OptionGroup', 'dialog', 'shared-settings', controller]
  });

  function controller ($log, $q, $rootScope, OptionGroup, dialog, sharedSettings) {
    $log.debug('Component: leave-request-action-dropdown');

    var vm = this;

    vm.actions = [];
    vm.actionLabels = {
      edit: 'Edit',
      cancel: 'Cancel',
      delete: 'Delete',
      view: 'View',
      respond: 'Respond',
      approve: 'Approve',
      reject: 'Reject'
    };

    /**
     * Performs an action on a given leave request
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {string} action
     */
    vm.act = function (action) {
      var map = {
        approve: {
          title: 'Approval',
          btnClass: 'success',
          btnLabel: 'Approve',
          msg: 'Please confirm approval'
        },
        cancel: {
          title: 'Cancellation',
          btnClass: 'danger',
          btnLabel: 'Confirm',
          msg: 'Please confirm cancellation'
        },
        delete: {
          title: 'Deletion',
          btnClass: 'danger',
          btnLabel: 'Confirm',
          msg: 'This cannot be undone'
        },
        reject: {
          title: 'Rejection',
          btnClass: 'warning',
          btnLabel: 'Reject',
          msg: 'Please confirm rejection'
        }
      };

      dialog.open({
        title: 'Confirm ' + map[action].title + '?',
        copyCancel: 'Cancel',
        copyConfirm: map[action].btnLabel,
        classConfirm: 'btn-' + map[action].btnClass,
        msg: map[action].msg,
        onConfirm: function () {
          return vm.request[action]()
            .then(function () {
              $rootScope.$emit('LeaveRequest::' + (action === 'delete' ? 'deleted' : 'edit'), vm.request);
            });
        }
      });
    };

    (function init () {
      vm.actions = getActions(vm.request, vm.role);
    }());

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
     * @param  {LeaveRequestInstance} request
     * @return {Boolean}
     */
    function canRequestBeCancelled (request, role) {
      var allowCancellationValue = vm.absenceTypes[request.type_id].allow_request_cancelation;

      if (role === 'staff') {
        return false;
      } else if (role === 'admin') {
        return true;
      }

      if (allowCancellationValue === '3') {
        return moment().isBefore(request.from_date);
      }

      return allowCancellationValue === '2';
    }

    /**
     * @TODO This function utilises external resource
     * vm.requestStatuses - this sould be refactored
     *
     * Defines which actions can be taken with the
     * leave request basing on its status, user role
     *
     * @param  {LeaveRequestInstance} request
     * @param  {string} role (staff|manager|admin)
     * @return {Array} allowed actions
     */
    function getActions (request, role) {
      var actionMatrix = {};
      var sn = sharedSettings.statusNames;

      actionMatrix[sn.awaitingApproval] = ['edit', 'approve', 'reject', 'cancel', 'delete'];
      actionMatrix[sn.moreInformationRequired] = ['respond', 'cancel', 'delete'];
      actionMatrix[sn.approved] = ['view', 'delete'];
      actionMatrix[sn.cancelled] = ['view', 'delete'];
      actionMatrix[sn.rejected] = ['view', 'delete'];

      var actions = actionMatrix[vm.requestStatuses[request.status_id].name];

      if (!canRequestBeCancelled(request, role)) {
        _.pull(actions, 'cancel');
      }

      if (role !== 'admin') {
        _.pull(actions, 'delete');
      }

      if (role === 'staff') {
        _.pull(actions, 'approve', 'reject');
      } else {
        // Inverts Edit and Respond actions for Admin and Manager
        actions = _.map(actions, function (action) {
          if (action === 'edit') { return 'respond'; }
          if (action === 'respond') { return 'edit'; }
          return action;
        });
      }

      return actions;
    }
  }
});
