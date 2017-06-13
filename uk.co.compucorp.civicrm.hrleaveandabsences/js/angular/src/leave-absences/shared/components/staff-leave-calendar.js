/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl'
], function (_, moment, components) {
  components.component('staffLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/staff-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$controller', '$log', '$rootScope', 'Calendar', controller]
  });

  function controller ($controller, $log, $rootScope, Calendar) {
    $log.debug('Component: staff-leave-calendar');

    var parentCtrl = $controller('CalendarCtrl');
    var vm = Object.create(parentCtrl);

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
      vm._resetMonths();
      vm._loadLeaveRequestsAndCalendar();
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
      vm.leaveRequests = {};

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
      return parentCtrl._loadLeaveRequestsAndCalendar.call(vm, 'contact_id', false);
    };

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     *
     * @param  {object} calendar
     */
    vm._setCalendarProps = function (calendar) {
      var leaveRequest;
      var monthData = _.clone(vm.months);

      _.each(calendar.days, function (dateObj) {
        // fetch leave request, search by date
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

    (function init () {
      vm._init();

      $rootScope.$on('LeaveRequest::new', vm.refresh);
      $rootScope.$on('LeaveRequest::edit', vm.refresh);
    })();

    return vm;
  }
});
