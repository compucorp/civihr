define([
  'common/lodash',
  'common/moment',
  'leave-absences/my-leave/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl'
], function (_, moment, components) {

  components.component('myLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$controller', '$log', '$q', 'Calendar', 'LeaveRequest', controller]
  });


  function controller($controller, $log, $q, Calendar, LeaveRequest) {
    $log.debug('Component: my-leave-calendar');

    var vm = Object.create($controller('CalendarCtrl'));

    vm.leaveRequests = {};

    /**
     * Returns the calendar information for a specific month
     *
     * @param  {object} monthObj
     * @return {array}
     */
    vm.getMonthData = function (monthObj) {
      var month;

      month = _.find(vm.months, function (month) {
        return (month.month === monthObj.month) && (month.year === monthObj.year);
      });

      return month ? month.data : [];
    };

    /**
     * Refresh all leave request and calendar data
     */
    vm.refresh = function () {
      vm.loading.calendar = true;
      vm._loadLeaveRequestAndCalendar()
        .then(function () {
          vm.loading.calendar = false;
        });
    };

    /**
     * Returns skeleton for the month object
     *
     * @param  {Object} startDate
     * @return {Object}
     */
    vm._getMonthSkeleton = function (startDate) {
      return {
        month: startDate.month(),
        year: startDate.year(),
        data: []
      };
    };

    /**
     * Index leave requests by date
     *
     * @param  {Array} leaveRequestsData - leave requests array from API
     */
    vm._indexLeaveRequests = function (leaveRequests) {
      _.each(leaveRequests, function (leaveRequest) {
        _.each(leaveRequest.dates, function (leaveRequestDate) {
          vm.leaveRequests[leaveRequestDate.date] = leaveRequest;
        });
      });
    };

    /**
     * Loads the calendar data
     *
     * @return {Promise}
     */
    vm._loadCalendar = function () {
      return Calendar.get(vm.contactId, vm.selectedPeriod.id)
        .then(function (calendar) {
          vm._setCalendarProps(calendar);
        });
    };

    /**
     * Loads all the leave requests and calls calendar load function
     *
     * @return {Promise}
     */
    vm._loadLeaveRequestAndCalendar = function () {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: {
          from: vm.selectedPeriod.start_date
        },
        to_date: {
          to: vm.selectedPeriod.end_date
        }
      })
      .then(function (leaveRequestsData) {
        vm._indexLeaveRequests(leaveRequestsData.list);

        return vm._loadCalendar();
      })
      .then(function () {
        vm._showMonthLoader();
      });
    };

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     *
     * @param  {object} calendar
     */
    vm._setCalendarProps = function (calendar) {
      var leaveRequest,
        monthData = _.clone(vm.months);

      _.each(calendar.days, function (dateObj) {
        //fetch leave request, search by date
        leaveRequest = vm.leaveRequests[dateObj.date];

        dateObj.UI = {
          isWeekend: calendar.isWeekend(vm._getDateObjectWithFormat(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(vm._getDateObjectWithFormat(dateObj.date)),
          isPublicHoliday: vm.isPublicHoliday(dateObj.date)
        };

        // set below props only if leaveRequest is found
        if (leaveRequest) {
          dateObj.UI.styles = vm._getStyles(leaveRequest, dateObj);
          dateObj.UI.isRequested = vm._isPendingApproval(leaveRequest);
          dateObj.UI.isAM = vm._isDayType('half_day_am', leaveRequest, dateObj.date);
          dateObj.UI.isPM = vm._isDayType('half_day_pm', leaveRequest, dateObj.date);
        }

        vm._getMonthObjectByDate(moment(dateObj.date), monthData).data.push(dateObj);
      });

      vm.months = monthData;
    };

    (function init() {
      vm.loading.page = true;
      //Select current month as default
      vm.selectedMonths = [vm.monthLabels[moment().month()]];
      $q.all([
        vm._loadAbsencePeriods(),
        vm._loadAbsenceTypes(),
        vm._loadPublicHolidays(),
        vm._loadStatuses(),
        vm._loadDayTypes()
      ])
      .then(function () {
        vm.legendCollapsed = false;
        return vm._loadLeaveRequestAndCalendar();
      })
      .finally(function () {
        vm.loading.page = false;
      });
    })();

    return vm;
  }
});
