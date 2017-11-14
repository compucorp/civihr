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

    var absenceType, calculationUnit, fromDateType, toDateType;
    var vm = this;

    vm.dates = { from: null, to: null };
    vm.label = '';
    vm.tooltipTemplate = null;

    vm.openLeavePopup = openLeavePopup;

    (function init () {
      watchForLeaveRequestReady();
    })();

    /**
     * Finds and stores the calculation unit for the leave request's
     * absence type
     */
    function findAbsenceTypeCalculationUnit () {
      calculationUnit = findRecordByIdFieldValue(
        vm.supportData.calculationUnits, 'value', absenceType.calculation_unit);
    }

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
     * Returns the template name for Leave Requests tooltips.
     *
     * The pattern is `type-[days|hours]-on-[single-date|multiple-dates]-tooltip`
     *
     * @return {String}
     */
    function getTooltipTemplateForLeaveRequests () {
      var dateRangeType, isSameDay;

      isSameDay = moment(vm.contactData.leaveRequest.from_date)
        .isSame(vm.contactData.leaveRequest.to_date, 'day');
      dateRangeType = isSameDay ? 'single-date' : 'multiple-dates';

      return 'type-' + calculationUnit.name + '-on-' +
        dateRangeType + '-tooltip';
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
      if (calculationUnit.name === 'days') {
        fromDateType = findRecordByIdFieldValue(vm.supportData.dayTypes,
          'value', vm.contactData.leaveRequest.from_date_type);
        toDateType = findRecordByIdFieldValue(vm.supportData.dayTypes,
          'value', vm.contactData.leaveRequest.to_date_type);

        vm.contactData.leaveRequest['from_date_type.label'] = fromDateType.label;
        vm.contactData.leaveRequest['to_date_type.label'] = toDateType.label;
      }
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
     * Copies dates from Leave Request, converts them from String
     * to Date type and sets to the component.
     */
    function resolveDayDates () {
      vm.dates.from = new Date(vm.contactData.leaveRequest.from_date);
      vm.dates.to = new Date(vm.contactData.leaveRequest.to_date);
    }

    /**
     * Determines the label for the day depending on the calculation unit or if
     * it's an accrued TOIL request.
     */
    function resolveDayLabel () {
      if (vm.contactData.isAccruedTOIL) {
        vm.label = 'AT';
      } else if (calculationUnit.name === 'days') {
        resolveDayLabelForDaysCalculationUnit();
      } else {
        resolveDayLabelForHoursCalculationUnit();
      }
    }

    /**
     * Determines the label for the day when calculation units are set to days.
     *
     * AM: leave requests for half day AM
     * PM: leave requests for half day PM
     * Otherwise leave empty.
     */
    function resolveDayLabelForDaysCalculationUnit () {
      vm.label = vm.contactData.isAM ? 'AM'
        : vm.contactData.isPM ? 'PM'
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
     */
    function resolveDayLabelForHoursCalculationUnit () {
      var sameDateAsFromDate = moment(vm.contactData.leaveRequest.from_date)
        .isSame(vm.date, 'day');
      var sameDateAsToDate = moment(vm.contactData.leaveRequest.to_date)
        .isSame(vm.date, 'day');

      vm.label = sameDateAsFromDate
        ? moment(vm.contactData.leaveRequest.from_date).format('HH:mm')
        : sameDateAsToDate
        ? moment(vm.contactData.leaveRequest.to_date).format('HH:mm')
        : '';
    }

    /**
     * Selects the tooltip template to use to display the leave request information.
     */
    function selectTooltipTemplate () {
      vm.tooltipTemplate = vm.contactData.isAccruedTOIL
        ? 'accrued-toil-tooltip'
        : getTooltipTemplateForLeaveRequests();
    }

    /**
     * Waits for the leave request to be accesible before mapping the necessary
     * leave request fields to it.
     */
    function watchForLeaveRequestReady () {
      $scope.$watch('day.contactData.leaveRequest', function () {
        if (vm.contactData && vm.contactData.leaveRequest) {
          mapLeaveRequestAbsenceType();
          findAbsenceTypeCalculationUnit();
          mapLeaveRequestDateTypes();
          resolveDayLabel();
          resolveDayDates();
          selectTooltipTemplate();
        }
      });
    }
  }
});
