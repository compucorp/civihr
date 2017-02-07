define([
  'common/lodash',
  'common/moment',
  'leave-absences/my-leave/modules/components'
], function (_, moment, components) {

  components.component('myLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$log', '$q', 'OptionGroup', 'AbsencePeriod', 'AbsenceType',
      'Calendar', 'LeaveRequest', 'PublicHoliday', controller]
  });


  function controller($log, $q, OptionGroup, AbsencePeriod, AbsenceType, Calendar, LeaveRequest, PublicHoliday) {
    $log.debug('Component: my-leave-calendar');

    var vm = Object.create(this),
      dayTypes = [],
      leaveRequests = [],
      publicHolidays = [],
      leaveRequestStatuses = [],
      serverDateFormat = 'YYYY-MM-DD';

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.calendar = {};
    vm.loading = {
      calendar: false,
      page: false
    };
    vm.months = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];
    vm.selectedMonths = [];
    vm.selectedPeriod = null;

    /**
     * Returns day name of the sent date(Monday, Tuesday etc.)
     *
     * @param  {string} date
     * @return {string}
     */
    vm.getDayName = function (date) {
      var day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      return day[getDateObjectWithFormat(date).day()];
    };

    /**
     * Returns the calendar information for a specific month
     *
     * @param  {int} month
     * @return {array}
     */
    vm.getMonthData = function (month) {
      if (vm.calendar.days) {
        var i,
          date,
          dates = Object.keys(vm.calendar.days),
          length = dates.length,
          datesForTheMonth = [];

        for (i = 0; i < length; i++) {
          date = moment(parseInt(dates[i]));
          if (date.month() === month) {
            datesForTheMonth.push(vm.calendar.days[dates[i]]);
          }
        }

        return datesForTheMonth;
      }
    };

    /**
     * Decides whether sent date is a public holiday
     *
     * @param  {string} date
     * @return {boolean}
     */
    vm.isPublicHoliday = function (date) {
      return !!publicHolidays[getDateObjectWithFormat(date).valueOf()];
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {AbsencePeriodInstance} period
     * @return {string}
     */
    vm.labelPeriod = function (period) {
      return period.current ? 'Current Period (' + period.title + ')' : period.title;
    };

    /**
     * Refresh all leave request and calendar data
     */
    vm.refresh = function () {
      vm.loading.calendar = true;
      loadLeaveRequestAndCalendar()
        .then(function () {
          vm.loading.calendar = false;
        });
    };

    (function init() {
      vm.loading.page = true;
      //Select current month as default
      vm.selectedMonths = [vm.months[moment().month()]];
      $q.all([
        loadAbsencePeriods(),
        loadAbsenceTypes(),
        loadPublicHolidays(),
        loadStatuses(),
        loadDayTypes()
      ])
      .then(function () {
        vm.legendCollapsed = false;
        return loadLeaveRequestAndCalendar();
      })
      .then(function () {
        vm.loading.page = false;
      });
    })();

    /**
     * Converts given date to moment object with server format
     *
     * @param {Date/String} date from server
     * @return {Date} Moment date
     */
    function getDateObjectWithFormat(date) {
      return moment(date, serverDateFormat).clone();
    }

    /**
     * Returns the leave request which is in range of the sent date
     *
     * @param  {string} date
     * @return {object}
     */
    function getLeaveRequestByDate(date) {
      var i,
        dates,
        length = leaveRequests.length;

      for (i = 0; i < length; i++) {
        dates = _.find(leaveRequests[i].dates, function (leaveRequestDate) {
          return moment(leaveRequestDate.date).isSame(date);
        });

        if (dates) {
          return leaveRequests[i];
        }
      }
    }

    /**
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @return {object}
     */
    function getStyles(leaveRequest) {
      var absenceType,
        status = leaveRequestStatuses[leaveRequest.status_id];

      if (status.name === 'waiting_approval'
        || status.name === 'approved'
        || status.name === 'admin_approved') {
        absenceType = _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id == leaveRequest.type_id;
        });

        return {
          backgroundColor: absenceType.color,
          borderColor: absenceType.color
        };
      }
    }

    /**
     * Returns whether a date is of a specific type
     * half_day_am or half_day_pm
     *
     * @param  {string} name
     * @param  {object} leaveRequest
     * @param  {string} date
     *
     * @return {boolean}
     */
    function isDayType(name, leaveRequest, date) {
      var dayType = dayTypes[name];

      if (moment(date).isSame(leaveRequest.from_date)) {
        return dayType.value == leaveRequest.from_date_type;
      }

      if (moment(date).isSame(leaveRequest.to_date)) {
        return dayType.value == leaveRequest.to_date_type;
      }
    }

    /**
     * Returns whether a leaveRequest is pending approval
     *
     * @param  {object} leaveRequest
     * @return {boolean}
     */
    function isPendingApproval(leaveRequest) {
      var status = leaveRequestStatuses[leaveRequest.status_id];

      return status.name === 'waiting_approval';
    }

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods() {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          vm.absencePeriods = absencePeriods;
          vm.selectedPeriod = _.find(vm.absencePeriods, function (period) {
            return !!period.current;
          });
        });
    }

    /**
     * Loads the absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes() {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }

    /**
     * Loads the calendar data
     *
     * @return {Promise}
     */
    function loadCalendar() {
      return Calendar.get(vm.contactId, vm.selectedPeriod.id)
        .then(function (calendar) {
          vm.calendar = setCalendarProps(calendar);
        });
    }

    /**
     * Loads the leave request day types
     *
     * @return {Promise}
     */
    function loadDayTypes() {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (dayTypesData) {
          dayTypes = _.indexBy(dayTypesData, 'name');
        });
    }

    /**
     * Loads all the leave requests and calls calendar load function
     *
     * @return {Promise}
     */
    function loadLeaveRequestAndCalendar() {
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
        leaveRequests = leaveRequestsData.list;
        return loadCalendar();
      });
    }

    /**
     * Loads all the public holidays
     *
     * @return {Promise}
     */
    function loadPublicHolidays() {
      return PublicHoliday.all()
        .then(function (publicHolidaysData) {
          var i,
            length = publicHolidaysData.length,
            datesObj = {};

          // convert to an object with time stamp as key
          for (i = 0; i < length; i++) {
            datesObj[getDateObjectWithFormat(publicHolidaysData[i].date).valueOf()] = publicHolidaysData[i];
          }

          publicHolidays = datesObj;
        });
    }

    /**
     * Loads the status option values
     *
     * @return {Promise}
     */
    function loadStatuses() {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          leaveRequestStatuses = _.indexBy(statuses, 'value');
        });
    }

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     *
     * @param  {object} calendar
     * @return {object}
     */
    function setCalendarProps(calendar) {
      var i,
        dates = Object.keys(calendar.days),
        length = dates.length,
        dateObj,
        leaveRequest;

      for (i = 0; i < length; i++) {
        dateObj = calendar.days[dates[i]];
        leaveRequest = getLeaveRequestByDate(dateObj.date);

        dateObj.UI = {
          isWeekend: calendar.isWeekend(getDateObjectWithFormat(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(getDateObjectWithFormat(dateObj.date)),
          isPublicHoliday: vm.isPublicHoliday(dateObj.date)
        };

        // set below props only if leaveRequest is found
        if (leaveRequest) {
          dateObj.UI.styles = getStyles(leaveRequest);
          dateObj.UI.isRequested = isPendingApproval(leaveRequest);
          dateObj.UI.isAM = isDayType('half_day_am', leaveRequest, dateObj.date);
          dateObj.UI.isPM = isDayType('half_day_pm', leaveRequest, dateObj.date);
        }
      }

      return calendar;
    }

    return vm;
  }
});
