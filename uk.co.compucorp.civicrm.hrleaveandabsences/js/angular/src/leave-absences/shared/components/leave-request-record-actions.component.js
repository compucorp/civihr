/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'common/services/hr-settings',
  'common/services/before-hash-query-params.service'
], function (components) {
  components.component('leaveRequestRecordActions', {
    bindings: {
      contactId: '<',
      selectedContactId: '<',
      isSelfRecord: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-record-actions.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', 'LeavePopup', 'beforeHashQueryParams', controller]
  });

  function controller ($log, LeavePopup, beforeHashQueryParams) {
    $log.debug('Component: leave-request-record-actions');

    var queryParams;
    var vm = this;

    vm.leaveRequestOptions = [
      { type: 'leave', icon: 'briefcase', label: 'Leave' },
      { type: 'sickness', icon: 'stethoscope', label: 'Sickness' }
    ];

    vm.openLeavePopup = openLeavePopup;

    /**
     * Automatically opens a request modal if the `openModal` param
     * is present in the query string
     */
    (function init () {
      queryParams = beforeHashQueryParams.parse();

      if (queryParams.openModal) {
        openLeavePopup(null, queryParams.openModal, vm.selectedContactId, vm.isSelfRecord);
      }
    }());

    /**
     * Opens the leave request popup
     *
     * @param {Object} leaveRequest
     * @param {String} leaveType
     * @param {String} selectedContactId
     * @param {Boolean} isSelfRecord
     */
    function openLeavePopup (leaveRequest, leaveType, selectedContactId, isSelfRecord) {
      LeavePopup.openModal.apply(LeavePopup, arguments);
    }
  }
});
