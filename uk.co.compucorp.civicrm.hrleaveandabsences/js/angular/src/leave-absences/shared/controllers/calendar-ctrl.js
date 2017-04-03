define([
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/public-holiday-model',
], function (controllers, _, moment) {
  'use strict';

  controllers.controller('CalendarCtrl', ['$timeout', 'shared-settings', 'AbsencePeriod', 'AbsenceType',
    'PublicHoliday', 'OptionGroup', controller]);

  function controller($timeout, sharedSettings, AbsencePeriod, AbsenceType, PublicHoliday, OptionGroup) {
    this._dayTypes = [];
    this._publicHolidays = [];
    this._leaveRequestStatuses = [];

    this.absencePeriods = [];
    this.absenceTypes = [];
    this.months = [];
    this.selectedMonths = [];
    this.selectedPeriod = null;
    this.loading = {
      calendar: false,
      page: false
    };
    this.monthLabels = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];

    /**
     * Fetch months from newly selected period and refresh data
     */
    this.changeSelectedPeriod = function () {
      this._fetchMonthsFromPeriod();
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
      var day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      return day[this._getDateObjectWithFormat(date).day()];
    };

    /**
     * Decides whether sent date is a public holiday
     *
     * @param  {string} date
     * @return {boolean}
     */
    this.isPublicHoliday = function (date) {
      return !!this._publicHolidays[this._getDateObjectWithFormat(date).valueOf()];
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
      return moment(date, sharedSettings.serverDateFormat).clone();
    };

    /**
     * Find the month which matches with the sent date
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
      var absenceType,
        status = this._leaveRequestStatuses[leaveRequest.status_id];

      if (status.name === 'waiting_approval'
        || status.name === 'approved'
        || status.name === 'admin_approved') {
        absenceType = _.find(this.absenceTypes, function (absenceType) {
          return absenceType.id == leaveRequest.type_id;
        });

        //If Balance change is positive, mark as Accrued TOIL
        if (leaveRequest.balance_change > 0) {
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
      var dayType = this._dayTypes[name];

      if (moment(date).isSame(leaveRequest.from_date)) {
        return dayType.value == leaveRequest.from_date_type;
      }

      if (moment(date).isSame(leaveRequest.to_date)) {
        return dayType.value == leaveRequest.to_date_type;
      }
    };

    /**
     * Returns whether a leaveRequest is pending approval
     *
     * @param  {object} leaveRequest
     * @return {boolean}
     */
    this._isPendingApproval = function (leaveRequest) {
      var status = this._leaveRequestStatuses[leaveRequest.status_id];

      return status.name === 'waiting_approval';
    };

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    this._loadAbsencePeriods = function () {
      var self = this;

      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          self.absencePeriods = absencePeriods;
          self.selectedPeriod = _.find(self.absencePeriods, function (period) {
            return !!period.current;
          });

          self._fetchMonthsFromPeriod();
        });
    };

    /**
     * Loads the absence types
     *
     * @return {Promise}
     */
    this._loadAbsenceTypes = function () {
      var self = this;

      return AbsenceType.all()
        .then(function (absenceTypes) {
          self.absenceTypes = absenceTypes;
        });
    };

    /**
     * Loads the leave request day types
     *
     * @return {Promise}
     */
    this._loadDayTypes = function () {
      var self = this;

      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (dayTypesData) {
          self._dayTypes = _.indexBy(dayTypesData, 'name');
        });
    };

    /**
     * Loads all the public holidays
     *
     * @return {Promise}
     */
    this._loadPublicHolidays = function () {
      var self = this;

      return PublicHoliday.all()
        .then(function (publicHolidaysData) {
          var datesObj = {};

          // convert to an object with time stamp as key
          publicHolidaysData.forEach(function (publicHoliday) {
            datesObj[self._getDateObjectWithFormat(publicHoliday.date).valueOf()] = publicHoliday;
          });

          self._publicHolidays = datesObj;
        });
    };

    /**
     * Loads the status option values
     *
     * @return {Promise}
     */
    this._loadStatuses = function () {
      var self = this;

      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          self._leaveRequestStatuses = _.indexBy(statuses, 'value');
        });
    };

    /**
     * Show month loader for all months initially
     * then hide each loader on the interval of an offset value
     */
    this._showMonthLoader = function () {
      var monthLoadDelay = 500,
        offset = 0,
        self = this;

      self.months.forEach(function (month) {
        // immediately show the current month...
        month.loading = month.label !== self.selectedMonths[0];

        //delay other months
        if (month.loading) {
          $timeout(function () {
            month.loading = false;
          }, offset);

          offset += monthLoadDelay;
        }
      });
    };

    return this;
  }
});
