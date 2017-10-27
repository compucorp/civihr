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

    var absenceType, fromDateType, toDateType;
    var vm = this;

    vm.openLeavePopup = openLeavePopup;

    (function init () {
      watchForLeaveRequestReady();
    })();

    /**
     * Given an array of records, finds and returns the one that matches the
     * id field with the comparison value.
     *
     * @param {Array} records - An array of objects to filter.
     * @param {String} idFieldName - the name for the ID field.
     * @param {any} value - The comparison value to match against the ID.
     *
     * @return {Object}
     */
    function findRecordByIdFieldValue (records, idFieldName, value) {
      return _.find(records, function (record) {
        return +record[idFieldName] === +value;
      });
    }

    /**
     * Maps the absence type title to the leave request.
     */
    function mapLeaveRequestAbsenceType () {
      absenceType = findRecordByIdFieldValue(vm.supportData.absenceTypes,
        'id', vm.contactData.leaveRequest.type_id);

      vm.contactData.leaveRequest['type_id.title'] = absenceType.title;
    }

    /**
     * Maps the from and to date type labels to the leave request.
     */
    function mapLeaveRequestDateTypes () {
      fromDateType = findRecordByIdFieldValue(vm.supportData.dayTypes,
        'value', vm.contactData.leaveRequest.from_date_type);
      toDateType = findRecordByIdFieldValue(vm.supportData.dayTypes,
        'value', vm.contactData.leaveRequest.to_date_type);

      vm.contactData.leaveRequest['from_date_type.label'] = fromDateType.label;
      vm.contactData.leaveRequest['to_date_type.label'] = toDateType.label;
    }

    /**
     * Maps missing fields from the leave request to use them in the tooltip's
     * template.
     */
    function mapLeaveRequestFields () {
      mapLeaveRequestAbsenceType();
      mapLeaveRequestDateTypes();
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
        if (vm.contactData.leaveRequest) {
          mapLeaveRequestFields();
        }
      });
    }
  }
});
