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
      watchLeaveRequests();
    })();

    /**
     * Opens the leave request popup
     *
     * @param {Object} event
     * @param {Object} leaveRequest
     * @param {String} leaveType
     * @param {String} selectedContactId
     * @param {Boolean} isSelfRecord
     */
    function openLeavePopup (event, leaveRequest, leaveType, selectedContactId, isSelfRecord) {
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
      return leaveRequestAttributes.isAM
        ? 'AM'
        : leaveRequestAttributes.isPM
          ? 'PM'
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
     * Sets absence types titles to leave requests attributes
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {Object} leaveRequestAttributes
     */
    function resolveLeaveRequestAbsenceTypeTitle (leaveRequest, leaveRequestAttributes) {
      vm.contactData.leaveRequestsAttributes[leaveRequest.id].absenceTypeTitle =
        _.find(vm.supportData.absenceTypes, { id: leaveRequest.type_id }).title;
    }

    /**
     * Sets a unit name to the leave requests attributes
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {Object} leaveRequestAttributes
     */
    function resolveLeaveRequestCalculationUnit (leaveRequest, leaveRequestAttributes) {
      var absenceType = _.find(vm.supportData.absenceTypes, { id: leaveRequest.type_id });
      var calculationUnit = _.find(vm.supportData.calculationUnits, { 'value': absenceType.calculation_unit });

      leaveRequestAttributes.unit = calculationUnit.name;
    }

    /**
     * Sets dates from leave requests to leave requests attributes
     * by converting them from String to Date type.
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {Object} leaveRequestAttributes
     */
    function resolveLeaveRequestDates (leaveRequest, leaveRequestAttributes) {
      leaveRequestAttributes.from_date = moment(leaveRequest.from_date).toDate();
      leaveRequestAttributes.to_date = moment(leaveRequest.to_date).toDate();
    }

    /**
     * Sets the from and to date type labels to the leave requests attributes.
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {Object} leaveRequestAttributes
     */
    function resolveLeaveRequestDateTypes (leaveRequest, leaveRequestAttributes) {
      if (leaveRequestAttributes.unit !== 'days') {
        return;
      }

      leaveRequestAttributes['from_date_type'] =
        _.find(vm.supportData.dayTypes, { value: leaveRequest.from_date_type }).label;
      leaveRequestAttributes['to_date_type'] =
        _.find(vm.supportData.dayTypes, { value: leaveRequest.to_date_type }).label;
    }

    /**
     * Determines a label for the leave request depending on the calculation unit
     * or if it's an accrued TOIL request.
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {Object} leaveRequestAttributes
     */
    function resolveLeaveRequestLabel (leaveRequest, leaveRequestAttributes) {
      var label = '';

      if (leaveRequestAttributes.isAccruedTOIL) {
        label = 'AT';
      } else if (leaveRequestAttributes.unit === 'days') {
        label = resolveDayLabelForDaysCalculationUnit(leaveRequestAttributes);
      } else {
        label = resolveDayLabelForHoursCalculationUnit(leaveRequest);
      }

      leaveRequestAttributes.label = label;
    }

    /**
     * Sets additional data needed for UI to leave requests attributes
     *
     * @param {LeaveRequestInstance} leaveRequest
     */
    function resolveLeaveRequestsAdditionalUIData (leaveRequest) {
      var leaveRequestAttributes =
        vm.contactData.leaveRequestsAttributes[leaveRequest.id];
      var resolvingFunctions = [
        resolveLeaveRequestCalculationUnit,
        resolveLeaveRequestDateTypes,
        resolveLeaveRequestLabel,
        resolveLeaveRequestDates,
        resolveLeaveRequestAbsenceTypeTitle
      ];

      resolvingFunctions.forEach(function (resolvingFunction) {
        resolvingFunction.call(this, leaveRequest, leaveRequestAttributes);
      });
    }

    /**
     * Waits for the leave request to be accesible before mapping the necessary
     * leave request fields to it.
     */
    function watchLeaveRequests () {
      $scope.$watch('day.contactData.leaveRequests', function () {
        if (vm.contactData && vm.contactData.leaveRequests) {
          vm.contactData.leaveRequests.forEach(resolveLeaveRequestsAdditionalUIData);
        }
      }, true);
    }
  }
});
