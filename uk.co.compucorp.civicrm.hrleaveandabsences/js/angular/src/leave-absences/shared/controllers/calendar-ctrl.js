/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/public-holiday-model'
], function (angular, _, moment, controllers) {
  'use strict';

  controllers.controller('CalendarCtrl', ['$q', '$rootScope', '$timeout',
    'shared-settings', 'AbsencePeriod', 'AbsenceType', 'LeaveRequest',
    'PublicHoliday', 'OptionGroup', 'Calendar', controller]);

  function controller ($q, $rootScope, $timeout, sharedSettings, AbsencePeriod, AbsenceType, LeaveRequest, PublicHoliday, OptionGroup, Calendar) {
    var dayTypes = [];
    var leaveRequestStatuses = [];
    var publicHolidays = [];

    var vm = this;
    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.contacts = [];
    vm.legendCollapsed = true;
    vm.leaveRequests = {};
    vm.months = [];
    vm.selectedMonths = null;
    vm.selectedPeriod = null;
    vm.loading = {
      calendar: true,
      page: true
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {object} absenceType
     * @return {object} style
     */
    vm.getAbsenceTypeStyle = function (absenceType) {
      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
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
     * Rebuilds the months list and reloads the selected months data
     * If the source of the refresh is a change in contacts filters, then
     * it reloads the contacts as well
     *
     * @param {string} source The source of the refresh (period or contacts change)
     */
    vm.refresh = function (source) {
      source = _.includes(['contacts', 'period'], source) ? source : 'period';

      vm.loading.calendar = true;

      $q.resolve()
        .then(buildPeriodMonthsList)
        .then((source === 'contacts' ? loadContacts : _.noop))
        .then(function () {
          vm.loading.calendar = false;
        })
        .then(loadSelectedMonthsData);
    };

    /**
     * Initialize the calendar
     *
     * @param {function} intermediateSteps
     */
    vm._init = function (intermediateSteps) {
      initListeners();
      initWatchers();

      $q.all([
        loadContacts(),
        loadAbsencePeriods(),
        loadAbsenceTypes(),
        loadPublicHolidays(),
        loadOptionValues()
      ])
      .then(function () {
        vm.legendCollapsed = false;
      })
      .then(function () {
        return intermediateSteps ? intermediateSteps() : null;
      })
      .then(function () {
        vm.loading.calendar = false;
      })
      .then(loadSelectedMonthsData)
      .then(function () {
        vm.loading.page = false;
      });
    };

    /**
     * Creates a list of all the months in the currently selected period
     */
    function buildPeriodMonthsList () {
      var months = [];
      var pointerDate = moment(vm.selectedPeriod.start_date).clone();
      var endDate = moment(vm.selectedPeriod.end_date);

      while (pointerDate.isBefore(endDate)) {
        months.push(monthStructure(pointerDate));
        pointerDate.add(1, 'month');
      }

      vm.months = months;
    }

    /**
     * Deletes the given leave request from the list, then
     * it re-processes the calendar's cell's data
     *
     * @param  {object} event
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function deleteLeaveRequest (event, leaveRequest) {
      vm.leaveRequests[leaveRequest.contact_id] = _.omit(
        vm.leaveRequests[leaveRequest.contact_id],
        function (leaveRequestObj) {
          return leaveRequestObj.id === leaveRequest.id;
        }
      );

      setMonthDaysProperties();
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
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @param  {object} dateObj - Date UI object which handles look of a calendar cell
     * @return {object}
     */
    function getStyles (leaveRequest, dateObj) {
      var absenceType;

      absenceType = _.find(vm.absenceTypes, function (absenceType) {
        return absenceType.id === leaveRequest.type_id;
      });

      if (leaveRequest.balance_change > 0) {
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
     * @return {Promise}
     */
    function indexLeaveRequests (leaveRequests) {
      var deferred = $q.defer();

      _.each(leaveRequests, function (leaveRequest) {
        vm.leaveRequests[leaveRequest.contact_id] = vm.leaveRequests[leaveRequest.contact_id] || {};

        _.each(leaveRequest.dates, function (leaveRequestDate) {
          vm.leaveRequests[leaveRequest.contact_id][leaveRequestDate.date] = leaveRequest;
        });
      });

      deferred.resolve();

      return deferred.promise;
    }

    /**
     * Initializes the event listeners
     */
    function initListeners () {
      $rootScope.$on('LeaveRequest::new', vm.refresh);
      $rootScope.$on('LeaveRequest::edit', vm.refresh);
      $rootScope.$on('LeaveRequest::updatedByManager', vm.refresh);
      $rootScope.$on('LeaveRequest::deleted', deleteLeaveRequest);
    }

    /**
     * Initializes the scope properties' watchers
     */
    function initWatchers () {
      $rootScope.$new().$watch(function () {
        return vm.selectedMonths;
      }, function (newValue, oldValue) {
        if (oldValue !== null && !angular.equals(newValue, oldValue)) {
          loadSelectedMonthsData();
        }
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
     * Decides whether sent date is a public holiday
     *
     * @param  {string} date
     * @return {boolean}
     */
    function isPublicHoliday (date) {
      return !!publicHolidays[getDateObjectWithFormat(date).valueOf()];
    }

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          vm.absencePeriods = _.sortBy(absencePeriods, 'start_date');
          vm.selectedPeriod = _.find(vm.absencePeriods, function (period) {
            return !!period.current;
          });
        })
        .then(function () {
          buildPeriodMonthsList();
        })
        .then(function () {
          setDefaultMonths();
        });
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
        vm.absenceTypes = absenceTypes;
      });
    }

    /**
     * Loads the calendar of each contact, for the given month
     *
     * @param {Object} month
     * @return {Promise}
     */
    function loadMonthWorkPatternCalendars (month) {
      var monthStartDate = month.days[0].date;
      var monthEndDate = month.days[month.days.length - 1].date;

      return Calendar.get(vm.contacts.map(function (contact) {
        return contact.id;
      }), monthStartDate, monthEndDate);
    }

    /**
     * Loads the contacts by using the `_.contacts` method in the child controller
     *
     * @return {Promise}
     */
    function loadContacts () {
      return vm._contacts().then(function (contacts) {
        vm.contacts = contacts;
      });
    }

    /**
     * Loads the work pattern calendar and the leave request of the given month,
     * then it process the data onto each day of the month
     *
     * @param  {Object} month
     * @return {Promise}
     */
    function loadMonthData (month) {
      return $q.all([
        loadMonthWorkPatternCalendars(month),
        loadMonthLeaveRequests(month)
      ])
      .then(function (results) {
        setMonthDaysProperties(month, results[0]);
      })
      .then(function () {
        month.loading = false;
      });
    }

    /**
     * Loads the approved/pending leave requests for the given month, limited
     * to the calendar contacts. It then indexes the leave requests
     *
     * @param {Object} month
     * @return {Promise}
     */
    function loadMonthLeaveRequests (month) {
      return LeaveRequest.all({
        from_date: { from: month.days[0].date },
        to_date: { to: month.days[month.days.length - 1].date },
        status_id: {'IN': [
          getLeaveStatusValuefromName(sharedSettings.statusNames.approved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.adminApproved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.awaitingApproval)
        ]},
        contact_id: { 'IN': vm.contacts.map(function (contact) {
          return contact.id;
        })}
      }, null, null, null, false)
      .then(function (leaveRequestsData) {
        return indexLeaveRequests(leaveRequestsData.list);
      });
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
        });
    }

    /**
     * Loads the data of the currently selected months
     * (or of all the months if none are selectd)
     *
     * @return {Promise}
     */
    function loadSelectedMonthsData () {
      var monthsToLoad = !vm.selectedMonths.length
        ? vm.months
        : vm.months.filter(function (month) {
          return _.includes(vm.selectedMonths, month.index);
        });

      return $q.all(monthsToLoad.map(loadMonthData));
    }

    /**
     * Returns the structure of the month of the given date
     *
     * @param  {Object} date
     * @return {Object}
     */
    function monthStructure (date) {
      return {
        loading: true,
        index: date.month(),
        year: date.year(),
        days: monthDaysStructure(date),
        name: {
          long: date.format('MMMM'),
          short: date.format('MMM')
        }
      };
    }

    /**
     * Returns the structure of the days list of the month of the given date
     *
     * @param  {object} date
     * @return {object}
     */
    function monthDaysStructure (date) {
      var currentDay = date.clone().startOf('month');

      return _.map(_.times(date.daysInMonth()), function () {
        var dayObj = {
          date: currentDay.format('YYYY-MM-DD'),
          name: currentDay.format('ddd'),
          index: currentDay.format('D'),
          enabled: currentDay.isSameOrAfter(vm.selectedPeriod.start_date) &&
            currentDay.isSameOrBefore(vm.selectedPeriod.end_date),
          contactsData: {}
        };

        currentDay.add(1, 'day');

        return dayObj;
      });
    }

    /**
     * Set the properties of the given day, for the contact of the given
     * work pattern calendar
     *
     * @param {Object} day
     * @param {CalendarInstance} workPatternCalendar
     */
    function setDayProperties (day, workPatternCalendar) {
      var contactData = day.contactsData[workPatternCalendar.contact_id] = {};

      return $q.all([
        workPatternCalendar.isWeekend(getDateObjectWithFormat(day.date)),
        workPatternCalendar.isNonWorkingDay(getDateObjectWithFormat(day.date))
      ])
      .then(function (results) {
        contactData.isWeekend = results[0];
        contactData.isNonWorkingDay = results[1];
        contactData.isPublicHoliday = isPublicHoliday(day.date);
      })
      .then(function () {
        var leaveRequest = vm.leaveRequests[workPatternCalendar.contact_id]
          ? vm.leaveRequests[workPatternCalendar.contact_id][day.date]
          : null;

        if (leaveRequest) {
          contactData.leaveRequest = leaveRequest;
          contactData.styles = getStyles(leaveRequest);
          contactData.isAccruedTOIL = leaveRequest.balance_change > 0;
          contactData.isRequested = isPendingApproval(leaveRequest);
          contactData.isAM = isDayType('half_day_am', leaveRequest, day.date);
          contactData.isPM = isDayType('half_day_pm', leaveRequest, day.date);
        }
      });
    }

    /**
     * Chooses the months that are to be selected by default
     */
    function setDefaultMonths () {
      var currentMonth = moment().month();

      vm.selectedMonths = [_.find(vm.months, function (month) {
        return month.index === currentMonth;
      }).index];
    }

    /**
     * For every day of the given month, it goes through each given contact's
     * work pattern calendar, and assigns the properties by which the day
     * will be marked on the calendar
     *
     * @param {Object} month
     * @param {Array} monthWorkPatternCalendars
     * @return {Promise}
     */
    function setMonthDaysProperties (month, monthWorkPatternCalendars) {
      return $q.all(month.days.map(function (day) {
        return $q.all(monthWorkPatternCalendars.map(function (calendar) {
          setDayProperties(day, calendar);
        }));
      }));
    }
  }
});
