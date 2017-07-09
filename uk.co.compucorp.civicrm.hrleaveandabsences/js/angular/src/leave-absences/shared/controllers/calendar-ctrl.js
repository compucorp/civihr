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
    'LeaveRequest', 'PublicHoliday', 'OptionGroup', controller]);

  function controller ($q, $timeout, sharedSettings, AbsencePeriod, AbsenceType, LeaveRequest, PublicHoliday, OptionGroup) {
    var dayTypes = [];
    var leaveRequestStatuses = [];
    var publicHolidays = [];

    this.absencePeriods = [];
    this.absenceTypes = [];
    this.leaveRequests = {};
    this.months = [];
    this.monthLabels = moment.monthsShort();
    this.selectedMonths = [];
    this.selectedPeriod = null;
    this.loading = {
      calendar: false,
      page: false
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
      return this._getDateObjectWithFormat(date).format('ddd');
    };

    /**
     * Decides whether sent date is a public holiday
     *
     * @param  {string} date
     * @return {boolean}
     */
    this.isPublicHoliday = function (date) {
      return !!publicHolidays[this._getDateObjectWithFormat(date).valueOf()];
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
     * Converts given date to moment object with server format
     *
     * @param {Date/String} date from server
     * @return {Date} Moment date
     */
    this._getDateObjectWithFormat = function (date) {
      return moment(date, sharedSettings.serverDateFormat);
    };

    /**
     * Finds the month which matches with the sent date
     * and return the related object
     *
     * @param {object} date
     * @param {array} months
     * @return {object}
     */
    this._getMonthObjectByDate = function (date, months) {
      return _.find(months, function (month) {
        return (month.month === date.month()) && (month.year === date.year());
      });
    };

    /**
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @param  {object} dateObj - Date UI object which handles look of a calendar cell
     * @return {object}
     */
    this._getStyles = function (leaveRequest, dateObj) {
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
    };

    /**
     * Initialize the calendar
     *
     * @param {function} intermediateSteps
     */
    this._init = function (intermediateSteps) {
      this.loading.page = true;
      // Select current month as default
      this.selectedMonths = [this.monthLabels[moment().month()]];

      $q.all([
        loadAbsencePeriods.call(this),
        loadAbsenceTypes.call(this),
        loadPublicHolidays.call(this),
        loadOptionValues()
      ])
      .then(function () {
        return intermediateSteps ? intermediateSteps() : null;
      })
      .then(function () {
        this.legendCollapsed = false;

        return this._loadLeaveRequestsAndCalendar();
      }.bind(this))
      .finally(function () {
        this.loading.page = false;
      }.bind(this));
    };

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
    this._isDayType = function (name, leaveRequest, date) {
      var dayType = dayTypes[name];

      if (moment(date).isSame(leaveRequest.from_date)) {
        return dayType.value === leaveRequest.from_date_type;
      }

      if (moment(date).isSame(leaveRequest.to_date)) {
        return dayType.value === leaveRequest.to_date_type;
      }
    };

    /**
     * Returns whether a leaveRequest is pending approval
     *
     * @param  {object} leaveRequest
     * @return {boolean}
     */
    this._isPendingApproval = function (leaveRequest) {
      var status = leaveRequestStatuses[leaveRequest.status_id];

      return status.name === sharedSettings.statusNames.awaitingApproval;
    };

    /**
     * Loads the approved, admin_approved and waiting approval leave requests and calls calendar load function
     *
     * @param {string} contactParamName - contact parameter key name
     * @param {boolean} cache
     * @param {function} intermediateSteps
     * @return {Promise}
     */
    this._loadLeaveRequestsAndCalendar = function (contactParamName, cache, intermediateSteps) {
      cache = cache === undefined ? true : cache;

      var params = {
        from_date: {from: this.selectedPeriod.start_date},
        to_date: {to: this.selectedPeriod.end_date},
        status_id: {'IN': [
          getLeaveStatusValuefromName(sharedSettings.statusNames.approved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.adminApproved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.awaitingApproval)
        ]}
      };
      params[contactParamName] = this.contactId;

      return LeaveRequest.all(params, null, null, null, cache)
        .then(function (leaveRequestsData) {
          this._indexLeaveRequests(leaveRequestsData.list);

          return this._loadCalendar();
        }.bind(this))
        .then(function () {
          intermediateSteps && intermediateSteps();
          this.loading.calendar = false;
          showMonthLoader.call(this);
        }.bind(this));
    };

    /**
     * Reset the months data for before refresh
     */
    this._resetMonths = function () {
      _.each(this.months, function (month) {
        month.data = [];
      });
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
        months.push(this._getMonthSkeleton(startDate));
        startDate.add(1, 'month');
      }

      this.months = months;
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
            result[this._getDateObjectWithFormat(publicHoliday.date).valueOf()] = publicHoliday;
          }.bind(this), {});
        }.bind(this));
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
