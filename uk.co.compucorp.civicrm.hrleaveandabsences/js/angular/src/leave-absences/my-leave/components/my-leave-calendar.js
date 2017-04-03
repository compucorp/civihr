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
    controller: ['$log', '$q', '$timeout', 'shared-settings', 'AbsencePeriod', 'AbsenceType',
      'Calendar', 'LeaveRequest', 'PublicHoliday', 'OptionGroup', controller]
  });


  function controller(
    $log, $q, $timeout, sharedSettings, AbsencePeriod, AbsenceType,
    Calendar, LeaveRequest, PublicHoliday, OptionGroup) {
    $log.debug('Component: my-leave-calendar');

    var dayTypes = [],
      leaveRequests = [],
      publicHolidays = [],
      leaveRequestStatuses = [],
      vm = Object.create(this);

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.months = [];
    vm.selectedMonths = [];
    vm.selectedPeriod = null;
    vm.loading = {
      calendar: false,
      page: false
    };
    vm.monthLabels = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];


    /**
     * Fetch months from newly selected period and refresh data
     */
    vm.changeSelectedPeriod = function() {
      fetchMonthsFromPeriod();
      vm.refresh();
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {object} absenceType
     * @return {object} style
     */
    vm.getAbsenceTypeStyle = function(absenceType) {
      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
    };

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
      vm.selectedMonths = [vm.monthLabels[moment().month()]];
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
      .finally(function () {
        vm.loading.page = false;
      });
    })();

    /**
     * Fetch all the months from the current period and
     * save it in vm.months
     */
    function fetchMonthsFromPeriod () {
      var months = [],
        startDate = moment(vm.selectedPeriod.start_date),
        endDate = moment(vm.selectedPeriod.end_date);

      while (startDate.isBefore(endDate)) {
        months.push({
          month: startDate.month(),
          year: startDate.year(),
          data: []
        });
        startDate.add(1, 'month');
      }

      vm.months = months;
    }

    /**
     * Converts given date to moment object with server format
     *
     * @param {Date/String} date from server
     * @return {Date} Moment date
     */
    function getDateObjectWithFormat(date) {
      return moment(date, sharedSettings.serverDateFormat).clone();
    }

    /**
     * Find the month which matches with the sent date
     * and return the related object
     *
     * @param {object} date
     * @param {array} months
     * @return {object}
     */
    function getMonthObjectByDate(date, months) {
      return _.find(months, function (month) {
        return (month.month === date.month()) && (month.year === date.year());
      });
    }

    /**
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @param  {object} dateObj - Date UI object which handles look of a calendar cell
     * @return {object}
     */
    function getStyles(leaveRequest, dateObj) {
      var absenceType,
        status = leaveRequestStatuses[leaveRequest.status_id];

      if (status.name === 'waiting_approval'
        || status.name === 'approved'
        || status.name === 'admin_approved') {
        absenceType = _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id == leaveRequest.type_id;
        });

        //If Balance change is positive, mark as Accrued TOIL
        if(leaveRequest.balance_change > 0) {
          dateObj.UI.isAccruedTOIL = true;
          return {
            border: '1px solid ' + absenceType.color
          };
        }

        return {
          backgroundColor: absenceType.color,
          borderColor: absenceType.color
        };
      }
    }

    /**
     * Index leave requests by date
     *
     * @param  {Array} leaveRequestsData - leave requests array from API
     */
    function indexLeaveRequests(leaveRequestsData) {
      _.each(leaveRequestsData, function (leaveRequest) {
        _.each(leaveRequest.dates, function (leaveRequestDate) {
          leaveRequests[leaveRequestDate.date] = leaveRequest;
        });
      });
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

          fetchMonthsFromPeriod();
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
          setCalendarProps(calendar);
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
        indexLeaveRequests(leaveRequestsData.list);

        return loadCalendar();
      })
      .then(function () {
        showMonthLoader();
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
          var datesObj = {};

          // convert to an object with time stamp as key
          publicHolidaysData.forEach(function (publicHoliday) {
            datesObj[getDateObjectWithFormat(publicHoliday.date).valueOf()] = publicHoliday;
          });

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
     */
    function setCalendarProps(calendar) {
      var leaveRequest,
        monthData = _.clone(vm.months);

      _.each(calendar.days, function (dateObj) {
        //fetch leave request, search by date
        leaveRequest = leaveRequests[dateObj.date];

        dateObj.UI = {
          isWeekend: calendar.isWeekend(getDateObjectWithFormat(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(getDateObjectWithFormat(dateObj.date)),
          isPublicHoliday: vm.isPublicHoliday(dateObj.date)
        };

        // set below props only if leaveRequest is found
        if (leaveRequest) {
          dateObj.UI.styles = getStyles(leaveRequest, dateObj);
          dateObj.UI.isRequested = isPendingApproval(leaveRequest);
          dateObj.UI.isAM = isDayType('half_day_am', leaveRequest, dateObj.date);
          dateObj.UI.isPM = isDayType('half_day_pm', leaveRequest, dateObj.date);
        }

        getMonthObjectByDate(moment(dateObj.date), monthData).data.push(dateObj);
      });

      vm.months = monthData;
    }

    /**
     * Show month loader for all months initially
     * then hide each loader on the interval of an offset value
     */
    function showMonthLoader() {
      var monthLoadDelay = 500,
        offset = 0;

      vm.months.forEach(function (month) {
        // immediately show the current month...
        month.loading = month.label !== vm.selectedMonths[0];

        //delay other months
        if (month.loading) {
          $timeout(function () {
            month.loading = false;
          }, offset);

          offset += monthLoadDelay;
        }
      });
    }

    return vm;
  }
});
