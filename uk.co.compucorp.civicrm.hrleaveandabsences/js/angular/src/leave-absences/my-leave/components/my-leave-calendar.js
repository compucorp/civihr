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
    controller: ['$controller', '$log', 'Calendar', controller]
  });

  function controller($controller, $log, Calendar) {
    $log.debug('Component: my-leave-calendar');

    var parentCtrl = $controller('CalendarCtrl'),
      vm = Object.create(parentCtrl);

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
      vm._loadLeaveRequestsAndCalendar()
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
     * @param  {Array} leaveRequests - leave requests array from API
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
    vm._loadLeaveRequestsAndCalendar = function () {
      return parentCtrl._loadLeaveRequestsAndCalendar.call(vm, 'contact_id', true)
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
      vm._init();
    })();

    return vm;
  }
});
