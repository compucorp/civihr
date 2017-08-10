/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (components) {
  components.component('leaveRequestRecordActions', {
    bindings: {
      btnClass: '@',
      contactId: '<',
      selectedContactId: '<',
      isSelfRecord: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-record-actions.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', 'LeavePopup', controller]
  });

  function controller ($log, LeavePopup) {
    $log.debug('Component: leave-request-record-actions');

    var vm = this;

    vm.leaveRequestOptions = [
      { type: 'leave', icon: 'briefcase', label: 'Leave' },
      { type: 'sickness', icon: 'stethoscope', label: 'Sickness' },
      { type: 'toil', icon: 'calendar-plus-o', label: 'Overtime' }
    ];

    vm.openLeavePopup = openLeavePopup;

    /**
     * Opens the leave request popup
     *
     * @param {Object} leaveRequest
     * @param {String} leaveType
     * @param {String} selectedContactId
     * @param {Boolean} isSelfRecord
     */
    function openLeavePopup (leaveRequest, leaveType, selectedContactId, isSelfRecord) {
      LeavePopup.openModal(leaveRequest, leaveType, selectedContactId, isSelfRecord);
    }
  }
});
