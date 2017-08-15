/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('leaveCalendarDay', {
    bindings: { contactData: '<' },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-calendar-day.html';
    }],
    controllerAs: 'day',
    controller: LeaveCalendarDayController
  });

  LeaveCalendarDayController.$inject = ['$log', 'LeavePopup'];

  function LeaveCalendarDayController ($log, LeavePopup) {
    'use strict';
    $log.debug('Component: leave-calendar-day');

    var vm = this;

    vm.openLeavePopup = openLeavePopup;

    /**
     * Opens the leave request popup
     *
     * When leave-request-actions.component sits inside manage-request component's table rows,
     * and the table row has a click event to open leave request, so event.stopPropagation()
     * is necessary to prevent the parents click event from being called
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
  }
});
