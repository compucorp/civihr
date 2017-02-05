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
    controllerAs: 'ctrl',
    controller: ['$log', '$q', 'OptionGroup', 'AbsencePeriod', 'AbsenceType',
      'Calendar', 'PublicHoliday', 'LeaveRequest', controller]
  });


  function controller($log, $q, OptionGroup, AbsencePeriod, AbsenceType, Calendar, PublicHoliday, LeaveRequest) {
    $log.debug('Component: my-leave-calendar');

    var vm = Object.create(this),
      dayTypes = [],
      leaveRequests = [],
      publicHolidays = [],
      leaveRequestStatuses = [];

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.calendar = {};
    vm.loading = false;
    vm.months = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];
    //Select current month as default
    vm.selectedMonths = [vm.months[new Date().getMonth()]];
    vm.selectedPeriod = null;

    /**
     * Decides whether sent date is a public holiday
     *
     * @param  {string} date
     * @return {boolean}
     */
    vm.isPublicHoliday = function (date) {
      return !!publicHolidays[new Date(date).getTime()];
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
     * Returns day name of the sent date(Monday, Tuesday etc.)
     *
     * @param  {string} date
     * @return {string}
     */
    vm.getDayName = function (date) {
      return new Date(date).toString().substr(0,3);
    };

    /**
     * Returns the calendar information for a specific month
     *
     * @param  {int} month
     * @return {array}
     */
    vm.getMonthData = function (month) {
      if (vm.calendar.days) {
        var iterator,
          dates = Object.keys(vm.calendar.days),
          date,
          length = dates.length,
          datesForTheMonth = [];

        for (iterator = 0; iterator < length; iterator++) {
          date = new Date(parseInt(dates[iterator]));
          if (date.getMonth() === month) {
            datesForTheMonth.push(vm.calendar.days[dates[iterator]]);
          }
        }

        return datesForTheMonth;
      }
    };

    /**
     * Refresh all leave request and calendar data
     */
    vm.refresh = function () {
      vm.loading = true;
      loadLeaveRequest()
        .then(function () {
          vm.loading = false;
        });
    };

    (function init() {
      vm.loading = true;
      $q.all([
        loadAbsencePeriods(),
        loadAbsenceTypes(),
        loadPublicHolidays(),
        loadStatuses(),
        loadDayTypes()
      ]).then(function () {
          vm.legendCollapsed = false;
          return loadLeaveRequest();
        })
        .then(function () {
          vm.loading = false;
        });
    })();

    /**
     * Returns the leave request which is in range of the sent date
     *
     * @param  {string} date
     * @return {object}
     */
    function getLeaveRequest(date) {
      var iterator,
        length = leaveRequests.length,
        dates;

      for (iterator = 0; iterator < length; iterator++) {
        dates = _.find(leaveRequests[iterator].dates, function (leaveRequestDate) {
          return moment(leaveRequestDate.date).isSame(date);
        });

        if (dates) {
          return leaveRequests[iterator];
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
      var status = leaveRequestStatuses[leaveRequest.status_id],
        absenceType;

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
          var iterator,
            length = dayTypesData.length,
            typesObj = {};

          // convert to an object with name as key
          for (iterator = 0; iterator < length; iterator++) {
            typesObj[dayTypesData[iterator].name] = dayTypesData[iterator];
          }
          dayTypes = typesObj;
        });
    }

    /**
     * Loads all the leave requests
     *
     * @return {Promise}
     */
    function loadLeaveRequest() {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: {
          from: vm.selectedPeriod.start_date
        },
        to_date: {
          to: vm.selectedPeriod.end_date
        }
      }).then(function (leaveRequestsData) {
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
          var iterator,
            length = publicHolidaysData.length,
            datesObj = {};

          // convert to an object with time stamp as key
          for (iterator = 0; iterator < length; iterator++) {
            datesObj[new Date(publicHolidaysData[iterator].date).getTime()] = publicHolidaysData[iterator];
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
          var iterator,
            length = statuses.length,
            statusesObj = {};

          // convert to an object with value as key
          for (iterator = 0; iterator < length; iterator++) {
            statusesObj[statuses[iterator].value] = statuses[iterator];
          }

          leaveRequestStatuses = statusesObj;
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
      var iterator,
        dates = Object.keys(calendar.days),
        length = dates.length,
        dateObj,
        leaveRequest;

      for (iterator = 0; iterator < length; iterator++) {
        dateObj = calendar.days[dates[iterator]];
        leaveRequest = getLeaveRequest(dateObj.date);

        dateObj.UI = {
          isWeekend: calendar.isWeekend(new Date(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(new Date(dateObj.date)),
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
