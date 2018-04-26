/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components'
], function (_, moment, components) {
  components.component('leaveCalendarDay', {
    bindings: {
      contactData: '<',
      date: '<',
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
     * Gets the absence type for the leave request.
     */
    function getLeaveRequestAbsenceType (leaveRequest) {
      return findRecordByIdFieldValue(vm.supportData.absenceTypes,
        'id', leaveRequest.type_id);
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
     * Determines the label for the day when calculation units are set to days.
     *
     * AM: leave requests for half day AM
     * PM: leave requests for half day PM
     * Otherwise leave empty.
     *
     * @param  {Object} leaveRequestAttributes
     * @return {String}
     */
    function resolveDayLabelForDaysCalculationUnit (leaveRequestAttributes) {
      return leaveRequestAttributes.isAM ? 'AM'
        : leaveRequestAttributes.isPM ? 'PM'
          : '';
    }

    /**
     * Determines the label for the day when calculation units are set to hours.
     *
     * If the date is the same as the start date of the request, the start time
     * is displayed.
     * If the date is the same as the end date of the request, the end time is
     * displayed.
     * Otherwise the label is empty.
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {String}
     */
    function resolveDayLabelForHoursCalculationUnit (leaveRequest) {
      var sameDateAsFromDate = moment(leaveRequest.from_date)
        .isSame(vm.date, 'day');
      var sameDateAsToDate = moment(leaveRequest.to_date)
        .isSame(vm.date, 'day');

      return sameDateAsFromDate
        ? moment(leaveRequest.from_date).format('HH:mm')
        : sameDateAsToDate
          ? moment(leaveRequest.to_date).format('HH:mm')
          : '';
    }

    /**
     * Determines a label for the leave request depending on the calculation unit
     * or if it's an accrued TOIL request.
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @param  {Object} leaveRequestAttributes
     * @return {String}
     */
    function resolveLeaveRequestLabel (leaveRequest, leaveRequestAttributes) {
      if (leaveRequestAttributes.isAccruedTOIL) {
        return 'AT';
      } else if (leaveRequestAttributes.unit === 'days') {
        return resolveDayLabelForDaysCalculationUnit(leaveRequestAttributes);
      } else {
        return resolveDayLabelForHoursCalculationUnit(leaveRequest);
      }
    }

    /**
     * Sets absence types titles to leave requests attributes
     */
    function resolveLeaveRequestsAbsenceTypesTitles () {
      vm.contactData.leaveRequests.forEach(function (leaveRequest) {
        vm.contactData.leaveRequestsAttributes[leaveRequest.id].absenceTypeTitle =
          getLeaveRequestAbsenceType(leaveRequest).title;
      });
    }

    /**
     * Determines labels for each leave request depending on the calculation unit
     * or if it's an accrued TOIL request and sets them to leave requests attributes.
     */
    function resolveLeaveRequestsLabels () {
      var leaveRequestAttributes;

      vm.contactData.leaveRequests.forEach(function (leaveRequest) {
        leaveRequestAttributes = vm.contactData.leaveRequestsAttributes[leaveRequest.id];
        leaveRequestAttributes.label =
          resolveLeaveRequestLabel(leaveRequest, leaveRequestAttributes);
      });
    }

    /**
     * Sets a unit name to the leave requests attributes
     */
    function resolveLeaveRequestsCalculationUnits () {
      var leaveRequestAttributes, absenceType, calculationUnit;

      vm.contactData.leaveRequests.forEach(function (leaveRequest) {
        leaveRequestAttributes = vm.contactData.leaveRequestsAttributes[leaveRequest.id];
        absenceType = _.find(vm.supportData.absenceTypes, { id: leaveRequest.type_id });
        calculationUnit = _.find(vm.supportData.calculationUnits, { 'value': absenceType.calculation_unit });
        leaveRequestAttributes.unit = calculationUnit.name;
      });
    }

    /**
     * Sets dates from leave requests to leave requests attributes
     * by converting them from String to Date type.
     */
    function resolveLeaveRequestsDates () {
      var leaveRequestAttributes;

      vm.contactData.leaveRequests.forEach(function (leaveRequest) {
        leaveRequestAttributes = vm.contactData.leaveRequestsAttributes[leaveRequest.id];
        leaveRequestAttributes.from_date =
          new Date(leaveRequest.from_date);
        leaveRequestAttributes.to_date =
          new Date(leaveRequest.to_date);
      });
    }

    /**
     * Sets the from and to date type labels to the leave requests attributes.
     */
    function resolveLeaveRequestsTypes () {
      var leaveRequestAttributes, fromDateType, toDateType;

      vm.contactData.leaveRequests.forEach(function (leaveRequest) {
        leaveRequestAttributes = vm.contactData.leaveRequestsAttributes[leaveRequest.id];

        if (leaveRequestAttributes.unit === 'days') {
          fromDateType = findRecordByIdFieldValue(vm.supportData.dayTypes,
            'value', leaveRequest.from_date_type);
          toDateType = findRecordByIdFieldValue(vm.supportData.dayTypes,
            'value', leaveRequest.to_date_type);

          leaveRequestAttributes['from_date_type'] = fromDateType.label;
          leaveRequestAttributes['to_date_type'] = toDateType.label;
        }
      });
    }

    /**
     * Waits for the leave request to be accesible before mapping the necessary
     * leave request fields to it.
     */
    function watchForLeaveRequestReady () {
      $scope.$watch('day.contactData.leaveRequests', function () {
        if (vm.contactData && vm.contactData.leaveRequests) {
          resolveLeaveRequestsCalculationUnits();
          resolveLeaveRequestsTypes();
          resolveLeaveRequestsLabels();
          resolveLeaveRequestsDates();
          resolveLeaveRequestsAbsenceTypesTitles();
        }
      }, true);
    }
  }
});
