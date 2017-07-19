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
    var calendars = [];

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
     * Fetches months from newly selected period and refresh data
     */
    vm.changeSelectedPeriod = function () {
      buildPeriodMonthsList();
      vm.refresh();
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
     * Refreshes all leave request and calendar data
     */
    vm.refresh = function () {
      loadContacts()
        .then(function () {
          return $q.all([
            loadCalendars(),
            loadLeaveRequests()
          ]);
        })
        .then(setCalendarProps);
    };

    /**
     * Initialize the calendar
     *
     * @param {function} intermediateSteps
     */
    vm._init = function (intermediateSteps) {
      initListeners();
      initWatchers();

      var pContactsPeriods = loadContactsAndAbsencePeriods();
      var pCalendars = pContactsPeriods.then(loadCalendars);
      var pLeaveRequests = $q.all([
        loadSupportData(),
        pContactsPeriods
      ])
      .then(loadLeaveRequests);

      $q.all([
        pCalendars,
        pLeaveRequests
      ])
      .then(setCalendarProps)
      .then(function () {
        vm.loading.calendar = false;
      })
      .then(function () {
        return intermediateSteps ? intermediateSteps() : null;
      })
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

      setCalendarProps();
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
          loadLeaveRequests().then(setCalendarProps);
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
     * Loads the calendar of each contact, for the currently selected period
     *
     * @return {Promise}
     */
    function loadCalendars () {
      return Calendar.get(vm.contacts.map(function (contact) {
        return contact.id;
      }), vm.selectedPeriod.id)
      .then(function (_calendars_) {
        calendars = _calendars_;
      });
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
     * Bundles the loading of the contacts and the absence periods
     *
     * @return {Promise}
     */
    function loadContactsAndAbsencePeriods () {
      return $q.all([
        loadContacts(),
        loadAbsencePeriods()
      ]);
    }

    /**
     * Loads the leave requests for each of the currently selected months
     * (or for all the months if none are selected), limited to the calendar contacts,
     * in the currently selected period. Then finally it indexes the leave requests
     *
     * @return {Promise}
     */
    function loadLeaveRequests () {
      var monthsToLoad = !vm.selectedMonths.length
        ? vm.months
        : vm.months.filter(function (month) {
          return _.includes(vm.selectedMonths, month.index);
        });

      return $q.all(monthsToLoad.map(function (month) {
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
      }));
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
     * Loads all the additional data that is needed for the calendar to function,
     * after which it displays the legend
     *
     * @return {Promise}
     */
    function loadSupportData () {
      return $q.all([
        loadAbsenceTypes(),
        loadPublicHolidays(),
        loadOptionValues()
      ])
      .then(function () {
        vm.legendCollapsed = false;
      });
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
     * Chooses the months that are to be selected by default
     */
    function setDefaultMonths () {
      var currentMonth = moment().month();

      vm.selectedMonths = [_.find(vm.months, function (month) {
        return month.index === currentMonth;
      }).index];
    }

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     */
    function setCalendarProps () {
      // TODO: Improve once we have calendars by month
      var monthsToLoad = !vm.selectedMonths.length
        ? vm.months
        : vm.months.filter(function (month) {
          return _.includes(vm.selectedMonths, month.index);
        });

      return $q.all(monthsToLoad.map(function (month) {
        return $q.all(month.days.map(function (day) {
          return $q.all(calendars.map(function (calendar) {
            var contactData = day.contactsData[calendar.contact_id] = {};
            var dayObj = _.find(calendar.days, function (calendarDay) {
              return calendarDay.date === day.date;
            });

            // TODO: Improve once we have calendars by month
            if (!dayObj) { return; }

            return $q.all([
              calendar.isWeekend(getDateObjectWithFormat(dayObj.date)),
              calendar.isNonWorkingDay(getDateObjectWithFormat(dayObj.date))
            ])
            .then(function (results) {
              contactData.isWeekend = results[0];
              contactData.isNonWorkingDay = results[1];
              contactData.isPublicHoliday = isPublicHoliday(dayObj.date);
            })
            .then(function () {
              // fetch leave request, first search by contact_id then by date
              var leaveRequest = vm.leaveRequests[calendar.contact_id] ? vm.leaveRequests[calendar.contact_id][dayObj.date] : null;

              if (leaveRequest) {
                contactData.leaveRequest = leaveRequest;
                contactData.styles = getStyles(leaveRequest);
                contactData.isAccruedTOIL = leaveRequest.balance_change > 0;
                contactData.isRequested = isPendingApproval(leaveRequest);
                contactData.isAM = isDayType('half_day_am', leaveRequest, dayObj.date);
                contactData.isPM = isDayType('half_day_pm', leaveRequest, dayObj.date);
              }
            });
          }));
        }))
        .then(function () {
          month.loading = false;
        });
      }));
    }
  }
});
