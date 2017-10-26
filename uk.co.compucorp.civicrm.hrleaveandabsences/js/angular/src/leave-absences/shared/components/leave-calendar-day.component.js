/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/components'
], function (_, components) {
  components.component('leaveCalendarDay', {
    bindings: {
      contactData: '<',
      supportData: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-calendar-day.html';
    }],
    controllerAs: 'day',
    controller: LeaveCalendarDayController
  });

  LeaveCalendarDayController.$inject = ['$log', '$scope', 'LeavePopup'];

  function LeaveCalendarDayController ($log, $scope, LeavePopup) {
    'use strict';
    $log.debug('Component: leave-calendar-day');

    var vm = this;

    vm.openLeavePopup = openLeavePopup;

    (function init () {
      watchForLeaveRequestReady();
    })();

    /**
     * Maps the absence type title to the leave request.
     */
    function mapLeaveRequestAbsenceType () {
      var absenceType = _.find(vm.supportData.absenceTypes, function (type) {
        return +type.id === +vm.contactData.leaveRequest.type_id;
      });

      vm.contactData.leaveRequest['type_id.title'] = absenceType.title;
    }

    /**
     * Maps missing fields from the leave request to use them in the tooltip
     * template.
     */
    function mapLeaveRequestFields () {
      mapLeaveRequestAbsenceType();
    }

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

    /**
     * Waits for the leave request to be accesible before mapping the necessary
     * leave request fields to it.
     */
    function watchForLeaveRequestReady () {
      $scope.$watch('day.contactData.leaveRequest', function () {
        vm.contactData.leaveRequest && mapLeaveRequestFields();
      });
    }
  }
});
