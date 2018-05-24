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

  LeaveCalendarDayController.$inject = ['$document', '$log', '$scope', '$timeout', 'LeavePopup'];

  function LeaveCalendarDayController ($document, $log, $scope, $timeout, LeavePopup) {
    'use strict';
    $log.debug('Component: leave-calendar-day');

    var vm = this;

    vm.tooltip = {
      show: false,
      day_cell_hovered: false,
      tooltip_hovered: false
    };

    vm.openLeavePopup = openLeavePopup;
    vm.toggleTooltip = toggleTooltip;

    (function init () {
      watchLeaveRequests();
    })();

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
      leaveRequestAttributes.from_date = new Date(leaveRequest.from_date);
      leaveRequestAttributes.to_date = new Date(leaveRequest.to_date);
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
     * Toggles tooltip for the day.
     * It reacts to entering/leaving either day cell or the tooltip itself,
     * if either of the elements are hovered, it remains the tooltip open.
     * It instantly shows a tooltip, but has a 100ms timeout to hide it once unhovered.
     *
     * @TODO this should be moved to a decorator to uib-tooltip
     *
     * @param {String} sourceElement day_cell|tooltip
     * @param {Boolean} isHovered
     */
    function toggleTooltip (sourceElement, isHovered, isForTouchScreen, event) {
      var isTouchScreen = 'ontouchstart' in $document[0].documentElement;

      if (event) {
        event.stopPropagation();
      }

      if (isForTouchScreen !== isTouchScreen) {
        return;
      }

      if (!isHovered) {
        $timeout(function () {
          vm.tooltip[sourceElement + '_hovered'] = isHovered;

          vm.tooltip.show =
            vm.tooltip.day_cell_hovered || vm.tooltip.tooltip_hovered;
        }, 100);
      } else {
        vm.tooltip[sourceElement + '_hovered'] = isHovered;

        vm.tooltip.show =
          vm.tooltip.day_cell_hovered || vm.tooltip.tooltip_hovered;
      }
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
