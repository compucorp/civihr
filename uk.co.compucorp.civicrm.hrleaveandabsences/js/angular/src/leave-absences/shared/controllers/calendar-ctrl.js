/* eslint-env amd */
define([
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/public-holiday-model'
], function (controllers, _, moment) {
  'use strict';

  controllers.controller('CalendarCtrl', ['$q', '$timeout', 'shared-settings', 'AbsencePeriod', 'AbsenceType',
    'LeaveRequest', 'PublicHoliday', 'OptionGroup', 'Calendar', controller]);

  function controller ($q, $timeout, sharedSettings, AbsencePeriod, AbsenceType, LeaveRequest, PublicHoliday, OptionGroup, Calendar) {
    var dayTypes = [];
    var leaveRequestStatuses = [];
    var publicHolidays = [];

    this.absencePeriods = [];
    this.absenceTypes = [];
    this.calendars = [];
    this.contacts = [];
    this.legendCollapsed = true;
    this.leaveRequests = {};
    this.months = [];
    this.monthLabels = moment.monthsShort();
    this.selectedMonths = [];
    this.selectedPeriod = null;
    this.loading = {
      calendar: true,
      page: true
    };

    /**
     * Fetches months from newly selected period and refresh data
     */
    this.changeSelectedPeriod = function () {
      fetchMonthsFromPeriod.call(this);
      this.refresh();
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {object} absenceType
     * @return {object} style
     */
    this.getAbsenceTypeStyle = function (absenceType) {
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
    this.getDayName = function (date) {
      return getDateObjectWithFormat(date).format('ddd');
    };

    /**
     * Returns the calendar information for a specific month
     *
     * @param  {object} monthObj
     * @return {array}
     */
    this.getMonthData = function (contact, monthObj) {
      var month = _.find(contact.calendarData, function (month) {
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
    this.isPublicHoliday = function (date) {
      return !!publicHolidays[getDateObjectWithFormat(date).valueOf()];
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {AbsencePeriodInstance} period
     * @return {string}
     */
    this.labelPeriod = function (period) {
      return period.current ? 'Current Period (' + period.title + ')' : period.title;
    };

    /**
     * Refreshes all leave request and calendar data
     */
    this.refresh = function () {
      this.loading.calendar = true;

      $q.all([
        loadCalendars.call(this),
        loadLeaveRequests.call(this)
      ])
      .then(fillCalendarCellsData.bind(this))
      .then(function () {
        this.loading.calendar = false;
      }.bind(this))
    };

    /**
     * Initialize the calendar
     *
     * @param {function} intermediateSteps
     */
    this._init = function (intermediateSteps) {
      setDefaultMonths.call(this);

      var pContactsPeriods = loadContactsAndAbsencePeriods.call(this);
      var pCalendars = pContactsPeriods.then(loadCalendars.bind(this));
      var pLeaveRequests = $q.all([
        loadSupporData.call(this),
        pContactsPeriods
      ])
      .then(loadLeaveRequests.bind(this));

      $q.all([
        pCalendars,
        pLeaveRequests
      ])
      .then(fillCalendarCellsData.bind(this))
      .then(function () {
        this.loading.calendar = false;
      }.bind(this))
      .then(function () {
        return intermediateSteps ? intermediateSteps() : null;
      })
      .then(function () {
        showMonthLoader.call(this);
        this.loading.page = false;
      }.bind(this))
    };

    /**
     * Fetch all the months from the current period and
     * save it in vm.months
     */
    function fetchMonthsFromPeriod () {
      var months = [];
      var startDate = moment(this.selectedPeriod.start_date);
      var endDate = moment(this.selectedPeriod.end_date);

      while (startDate.isBefore(endDate)) {
        months.push(getMonthSkeleton(startDate));
        startDate.add(1, 'month');
      }

      this.months = months;
    }

    /**
     * Fills the data of each "cell" of the calendar
     *
     * @return {Promise}
     */
    function fillCalendarCellsData () {
      return $q.all(this.calendars.map(setCalendarProps.bind(this)));
    }

    /**
     * Converts given date to moment object with server format
     *
     * @param {Date/String} date from server
     * @return {Date} Moment date
     */
    function getDateObjectWithFormat (date) {
      return moment(date, sharedSettings.serverDateFormat);
    }

    /**
     * Returns leave status value from name
     * @param {String} name - name of the leave status
     * @returns {int/boolean}
     */
    function getLeaveStatusValuefromName (name) {
      var leaveStatus = _.find(leaveRequestStatuses, function (status) {
        return status.name === name;
      });

      return leaveStatus ? leaveStatus.value : false;
    }

    /**
     * Finds the month which matches with the sent date
     * and return the related object
     *
     * @param {object} date
     * @param {array} months
     * @return {object}
     */
    function getMonthObjectByDate (date, months) {
      return _.find(months, function (month) {
        return (month.month === date.month()) && (month.year === date.year());
      });
    }

    /**
     * Returns skeleton for the month object
     *
     * @param  {Object} startDate
     * @return {Object}
     */
    function getMonthSkeleton (startDate) {
      return {
        month: startDate.month(),
        year: startDate.year()
      };
    }

    /**
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @param  {object} dateObj - Date UI object which handles look of a calendar cell
     * @return {object}
     */
    function getStyles (leaveRequest, dateObj) {
      var absenceType;

      dateObj.leaveRequest = leaveRequest;

      absenceType = _.find(this.absenceTypes, function (absenceType) {
        return absenceType.id === leaveRequest.type_id;
      });

      // If Balance change is positive, mark as Accrued TOIL
      if (leaveRequest.balance_change > 0) {
        dateObj.UI.isAccruedTOIL = true;

        return {
          borderColor: absenceType.color
        };
      }

      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
    }

    /**
     * Index leave requests by contact_id as first level
     * and date as second level
     *
     * @param  {Array} leaveRequests - leave requests array from API
     */
    function indexLeaveRequests (leaveRequests) {
      this.leaveRequests = {};

      _.each(leaveRequests, function (leaveRequest) {
        this.leaveRequests[leaveRequest.contact_id] = this.leaveRequests[leaveRequest.contact_id] || {};

        _.each(leaveRequest.dates, function (leaveRequestDate) {
          this.leaveRequests[leaveRequest.contact_id][leaveRequestDate.date] = leaveRequest;
        }.bind(this));
      }.bind(this));
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
    function isDayType (name, leaveRequest, date) {
      var dayType = dayTypes[name];

      if (moment(date).isSame(leaveRequest.from_date)) {
        return dayType.value === leaveRequest.from_date_type;
      }

      if (moment(date).isSame(leaveRequest.to_date)) {
        return dayType.value === leaveRequest.to_date_type;
      }
    }

    /**
     * Returns whether a leaveRequest is pending approval
     *
     * @param  {object} leaveRequest
     * @return {boolean}
     */
    function isPendingApproval (leaveRequest) {
      var status = leaveRequestStatuses[leaveRequest.status_id];

      return status.name === sharedSettings.statusNames.awaitingApproval;
    }

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          this.absencePeriods = _.sortBy(absencePeriods, 'start_date');
          this.selectedPeriod = _.find(this.absencePeriods, function (period) {
            return !!period.current;
          });

          fetchMonthsFromPeriod.call(this);
        }.bind(this));
    }

    /**
     * Loads the active absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all({
        is_active: true
      }).then(function (absenceTypes) {
        this.absenceTypes = absenceTypes;
      }.bind(this));
    }

    /**
     * Loads the calendar of each contact, for the currently selected period
     *
     * @return {Promise}
     */
    function loadCalendars () {
      return Calendar.get(this.contacts.map(function (contact) {
        return contact.id;
      }), this.selectedPeriod.id)
      .then(function (calendars) {
        this.calendars = calendars;
      }.bind(this));
    }

    /**
     * Loads the contacts by using the `_.contacts` method in the child controller
     *
     * @return {Promise}
     */
    function loadContacts () {
      return this._contacts().then(function (contacts) {
        this.contacts = contacts;
      }.bind(this));
    }


    /**
     * Bundles the loading of the contacts and the absence periods
     *
     * @return {Promise}
     */
    function loadContactsAndAbsencePeriods () {
      return $q.all([
        loadContacts.call(this),
        loadAbsencePeriods.call(this)
      ]);
    }

    /**
     * Loads the leave requests for the contacts, in the currently selected period,
     * then indexes the leave requests
     *
     * @return {Promise}
     */
    function loadLeaveRequests () {
      return LeaveRequest.all({
        from_date: { from: this.selectedPeriod.start_date },
        to_date: { to: this.selectedPeriod.end_date },
        status_id: {'IN': [
          getLeaveStatusValuefromName(sharedSettings.statusNames.approved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.adminApproved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.awaitingApproval)
        ]},
        contact_id: { 'IN': this.contacts.map(function (contact) {
          return contact.id;
        })}
      }, null, null, null, false)
      .then(function (leaveRequestsData) {
        indexLeaveRequests.call(this, leaveRequestsData.list);
      }.bind(this));
    }

    /**
     * Loads the OptionValues necessary for the controller
     *
     * @return {Promise}
     */
    function loadOptionValues () {
      return OptionGroup.valuesOf([
        'hrleaveandabsences_leave_request_status',
        'hrleaveandabsences_leave_request_day_type'
      ])
      .then(function (data) {
        leaveRequestStatuses = _.indexBy(data.hrleaveandabsences_leave_request_status, 'value');
        dayTypes = _.indexBy(data.hrleaveandabsences_leave_request_day_type, 'name');
      });
    }

    /**
     * Loads all the public holidays
     *
     * @return {Promise}
     */
    function loadPublicHolidays () {
      return PublicHoliday.all()
        .then(function (publicHolidaysData) {
          // convert to an object with time stamp as key
          publicHolidays = _.transform(publicHolidaysData, function (result, publicHoliday) {
            result[getDateObjectWithFormat(publicHoliday.date).valueOf()] = publicHoliday;
          }, {});
        }.bind(this));
    }

    /**
     * Loads all the additional data that is needed for the calendar to function,
     * after which it displays the legend
     *
     * @return {Promise}
     */
    function loadSupporData () {
      return $q.all([
        loadAbsenceTypes.call(this),
        loadPublicHolidays.call(this),
        loadOptionValues()
      ])
      .then(function () {
        this.legendCollapsed = false;
      }.bind(this))
    }

    /**
     * Chooses the months that are to be selected by default
     */
    function setDefaultMonths () {
      this.selectedMonths = [this.monthLabels[moment().month()]];
    }

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     */
    function setCalendarProps (calendar) {
      var monthData = _.map(this.months, function (month) {
        return _.extend(_.clone(month), { data: [] });
      });

      var contact = _.find(this.contacts, function (contact) {
        return contact.id === calendar.contact_id;
      });
      contact.calendarData = [];

      return $q.all(_.map(calendar.days, function (day) {
        return $q.all([
          calendar.isWeekend(getDateObjectWithFormat(day.date)),
          calendar.isNonWorkingDay(getDateObjectWithFormat(day.date))
        ])
        .then(function (results) {
          day.UI = {
            isWeekend: results[0],
            isNonWorkingDay: results[1],
            isPublicHoliday: this.isPublicHoliday(day.date)
          };
        }.bind(this))
        .then(function () {
          // fetch leave request, first search by contact_id then by date
          var leaveRequest = this.leaveRequests[calendar.contact_id] ? this.leaveRequests[calendar.contact_id][day.date] : null;

          if (leaveRequest) {
            day.UI.styles = getStyles.call(this, leaveRequest, day);
            day.UI.isRequested = isPendingApproval(leaveRequest);
            day.UI.isAM = isDayType('half_day_am', leaveRequest, day.date);
            day.UI.isPM = isDayType('half_day_pm', leaveRequest, day.date);
          }
        }.bind(this))
        .then(function () {
          getMonthObjectByDate(moment(day.date), monthData).data.push(day);
        })
        .then(function () {
          contact.calendarData = monthData;
        });
      }.bind(this)));
    }

    /**
     * Show month loader for all months initially
     * then hide each loader on the interval of an offset value
     */
    function showMonthLoader () {
      var monthLoadDelay = 500;
      var offset = 0;

      this.months.forEach(function (month) {
        // immediately show the current month...
        month.loading = month.label !== this.selectedMonths[0];

        // delay other months
        if (month.loading) {
          $timeout(function () {
            month.loading = false;
          }, offset);

          offset += monthLoadDelay;
        }
      }.bind(this));
    }
  }
});
