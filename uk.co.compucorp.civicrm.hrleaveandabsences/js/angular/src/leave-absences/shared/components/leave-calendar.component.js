/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/controllers/sub-controllers/leave-calendar-admin.controller',
  'leave-absences/shared/controllers/sub-controllers/leave-calendar-manager.controller',
  'leave-absences/shared/controllers/sub-controllers/leave-calendar-staff.controller'
], function (angular, _, moment, components) {
  components.component('leaveCalendar', {
    bindings: {
      contactId: '<',
      roleOverride: '@?'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$controller', '$q', '$log', '$rootScope', '$timeout',
      'shared-settings', 'AbsencePeriod', 'AbsenceType', 'LeaveRequest',
      'PublicHoliday', 'OptionGroup', 'Calendar', 'checkPermissions',
      controller]
  });

  function controller ($controller, $q, $log, $rootScope, $timeout, sharedSettings, AbsencePeriod, AbsenceType, LeaveRequest, PublicHoliday, OptionGroup, Calendar, checkPermissions) {
    $log.debug('Component: leave-calendar');

    var subController, userRole;

    var calendarsByMonthId = {};
    var dayTypes = [];
    var leaveRequestStatuses = [];
    var publicHolidays = [];

    var vm = this;
    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.contacts = [];
    vm.leaveRequests = {};
    vm.months = [];
    vm.selectedMonths = null;
    vm.selectedPeriod = null;
    vm.showFilters = false;
    vm.loading = { calendar: true, page: true };
    vm.filters = {
      optionValues: {},
      userSettings: {
        contact: null,
        contacts_with_leaves: false,
        department: null,
        level_type: null,
        location: null,
        region: null
      }
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
     * Reloads the selected months data
     *
     * If the source of the refresh is a period change, then
     * it rebuilds the months list as well
     * If the source of the refresh is a change in contacts filters, then
     * it reloads the contacts as well
     *
     * @param {string} source The source of the refresh (period or contacts change)
     */
    vm.refresh = function (source) {
      source = _.includes(['contacts', 'period'], source) ? source : 'period';

      vm.loading.calendar = true;

      $q.resolve()
        .then((source === 'period' ? buildPeriodMonthsList : _.noop))
        .then((source === 'contacts' ? loadContacts : _.noop))
        .then(function () {
          vm.loading.calendar = false;
        })
        .then(function () {
          // If the contacts list changed, all the months' data needs to be reloaded
          loadSelectedMonthsData((source === 'contacts'));
        });
    };

    // init
    (function init () {
      setUserRole().then(function () {
        initListeners();
        initWatchers();
      })
      .then(injectSubController)
      .then(function () {
        return $q.all([
          loadContacts(),
          loadAbsencePeriods(),
          loadAbsenceTypes(),
          loadPublicHolidays(),
          loadBasicOptionValues()
        ]);
      })
      .then(function () {
        return vm.showFilters ? loadFiltersOptionValues() : _.noop;
      })
      .then(function () {
        vm.loading.page = false;
        vm.loading.calendar = false;
      })
      .then(loadSelectedMonthsData);
    }());

    /**
     * Adds a leave request to the calendar
     *
     * @param {Object} event
     * @param {LeaveRequestInstance} leaveRequest
     */
    function addLeaveRequest (event, leaveRequest) {
      indexLeaveRequests([leaveRequest]);
      updateLeaveRequestDaysContactData(leaveRequest);
    }

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
     * Returns work pattern calendar of the given month of the given contact
     *
     * @param  {String} contactId
     * @param  {String} monthId
     * @return {Object}
     */
    function contactMonthWorkPatternCalendar (contactId, monthId) {
      return calendarsByMonthId[monthId][contactId];
    }

    /**
     * Converts given date to moment object with server format
     *
     * @param {Date/String} date from server
     * @return {Date} Moment date
     */
    function dateObjectWithFormat (date) {
      return moment(date, sharedSettings.serverDateFormat);
    }

    /**
     * Deletes the given leave request from the list
     *
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function deleteLeaveRequest (event, leaveRequest) {
      removeLeaveRequestFromIndexedList(leaveRequest);
      updateLeaveRequestDaysContactData(leaveRequest);
    }

    /**
     * Generate a unique id of the month of the given date
     *
     * @param  {Object} dateMoment
     * @return {String}
     */
    function generateMonthId (dateMoment) {
      return dateMoment.month() + '' + dateMoment.year();
    }

    /**
     * Returns the list of day objects corresponding to the dates the
     * given leave request spans
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {Array}
     */
    function leaveRequestDays (leaveRequest) {
      var days = [];
      var pointerDate = moment(leaveRequest.from_date).clone();
      var toDate = moment(leaveRequest.to_date);

      while (pointerDate.isSameOrBefore(toDate)) {
        days.push(_.find(monthFromDate(pointerDate).days, function (day) {
          return day.date === pointerDate.format('YYYY-MM-DD');
        }));

        pointerDate.add(1, 'day');
      }

      return days;
    }

    /**
     * Finds the given leave request in the internal indexed list
     *
     * @param  {LeaveRequestInstance} leaveRequest]
     * @return {LeaveRequestInstance}
     */
    function leaveRequestFromIndexedList (leaveRequest) {
      return _.find(vm.leaveRequests[leaveRequest.contact_id], function (leaveRequestOb) {
        return leaveRequest.id === leaveRequestOb.id;
      });
    }

    /**
     * Returns leave status value from name
     * @param {String} name - name of the leave status
     * @returns {int/boolean}
     */
    function leaveRequestStatusValueFromName (name) {
      var leaveStatus = _.find(leaveRequestStatuses, function (status) {
        return status.name === name;
      });

      return leaveStatus ? leaveStatus.value : false;
    }

    /**
     * Returns a month object starting from a date
     *
     * @param  {Object} dateMoment
     * @return {Object}
     */
    function monthFromDate (dateMoment) {
      var monthId = generateMonthId(dateMoment);

      return _.find(vm.months, function (month) {
        return month.id === monthId;
      });
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

      leaveRequests.forEach(function (leaveRequest) {
        var days = leaveRequest.dates ? leaveRequest.dates : leaveRequestDays(leaveRequest);

        vm.leaveRequests[leaveRequest.contact_id] = vm.leaveRequests[leaveRequest.contact_id] || {};

        days.forEach(function (day) {
          vm.leaveRequests[leaveRequest.contact_id][day.date] = leaveRequest;
        });
      });

      deferred.resolve();

      return deferred.promise;
    }

    /**
     * Initializes the event listeners
     */
    function initListeners () {
      $rootScope.$on('LeaveRequest::new', addLeaveRequest);
      $rootScope.$on('LeaveRequest::edit', updateLeaveRequest);
      $rootScope.$on('LeaveRequest::updatedByManager', updateLeaveRequest);
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
     * Injects the calendar sub controller specific for the role of the current user
     */
    function injectSubController () {
      subController = $controller('LeaveCalendar' + _.capitalize(userRole) + 'Controller').init(vm);
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
      return !!publicHolidays[dateObjectWithFormat(date).valueOf()];
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
        .then(buildPeriodMonthsList)
        .then(setDefaultMonths);
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
     * Loads the OptionValues necessary for basic functioning of the controller
     *
     * @return {Promise}
     */
    function loadBasicOptionValues () {
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
     * Loads the contacts by using the `_.contacts` method in the child controller
     *
     * @return {Promise}
     */
    function loadContacts () {
      return subController.loadContacts().then(function (contacts) {
        vm.contacts = contacts;
      });
    }

    /**
     * Loads the OptionValues necessary for the filters
     *
     * @return {Promise}
     */
    function loadFiltersOptionValues () {
      return OptionGroup.valuesOf([
        'hrjc_region',
        'hrjc_location',
        'hrjc_level_type',
        'hrjc_department'
      ])
      .then(function (data) {
        vm.filters.optionValues.regions = data.hrjc_region;
        vm.filters.optionValues.locations = data.hrjc_location;
        vm.filters.optionValues.levelTypes = data.hrjc_level_type;
        vm.filters.optionValues.departments = data.hrjc_department;
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
      month.loading = true;

      return $q.all([
        loadMonthWorkPatternCalendars(month),
        loadMonthLeaveRequests(month)
      ])
      .then(function () {
        return setMonthDaysContactData(month);
      })
      .then(function () {
        month.contactsDataLoaded = true;
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
          leaveRequestStatusValueFromName(sharedSettings.statusNames.approved),
          leaveRequestStatusValueFromName(sharedSettings.statusNames.adminApproved),
          leaveRequestStatusValueFromName(sharedSettings.statusNames.awaitingApproval)
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
      }), monthStartDate, monthEndDate)
      .then(function (monthCalendars) {
        calendarsByMonthId[month.id] = _.indexBy(monthCalendars, 'contact_id');
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
            result[dateObjectWithFormat(publicHoliday.date).valueOf()] = publicHoliday;
          }, {});
        });
    }

    /**
     * Loads the data of the currently selected months
     * (or of all the months if none are selectd)
     *
     * @param {boolean} forceReload if true, then it loads the data of all months
     *   regardless if the data was already loaded
     * @return {Promise}
     */
    function loadSelectedMonthsData (forceReload) {
      var monthsToLoad = !vm.selectedMonths.length
        ? vm.months
        : vm.months.filter(function (month) {
          return _.includes(vm.selectedMonths, month.index);
        });

      if (forceReload !== true) {
        monthsToLoad = monthsToLoad.filter(function (month) {
          return !month.contactsDataLoaded;
        });
      }

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
        id: generateMonthId(date),
        index: date.month(),
        year: date.year(),
        days: monthDaysStructure(date),
        contactsDataLoaded: false,
        loading: true,
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
     * Removes the given leave request from the internal indexed list
     *
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function removeLeaveRequestFromIndexedList (leaveRequest) {
      vm.leaveRequests[leaveRequest.contact_id] = _.omit(
        vm.leaveRequests[leaveRequest.contact_id],
        function (leaveRequestObj) {
          return leaveRequestObj.id === leaveRequest.id;
        }
      );
    }

    /**
     * Sets the properties of the given day, for the contact with the given id
     *
     * @param {Object} day
     * @param {String} contactId
     * @param {Boolean} leaveRequestPropertiesOnly updates only properties
     *   related to the contact's leave request on the day (if any)
     */
    function setDayContactData (day, contactId, leaveRequestPropertiesOnly) {
      var month, p, workPatternCalendar;

      day.contactsData[contactId] = day.contactsData[contactId] || {};

      month = monthFromDate(moment(day.date));
      workPatternCalendar = contactMonthWorkPatternCalendar(contactId, month.id);

      p = leaveRequestPropertiesOnly === true ? $q.resolve() : $q.all([
        workPatternCalendar.isWeekend(dateObjectWithFormat(day.date)),
        workPatternCalendar.isNonWorkingDay(dateObjectWithFormat(day.date))
      ])
      .then(function (results) {
        _.assign(day.contactsData[contactId], {
          isWeekend: results[0],
          isNonWorkingDay: results[1],
          isPublicHoliday: isPublicHoliday(day.date)
        });
      });

      return p.then(function () {
        return vm.leaveRequests[contactId] ? vm.leaveRequests[contactId][day.date] : null;
      })
      .then(function (leaveRequest) {
        _.assign(day.contactsData[contactId], {
          leaveRequest: leaveRequest || null,
          styles: leaveRequest ? styles(leaveRequest) : null,
          isAccruedTOIL: leaveRequest ? leaveRequest.balance_change > 0 : null,
          isRequested: leaveRequest ? isPendingApproval(leaveRequest) : null,
          isAM: leaveRequest ? isDayType('half_day_am', leaveRequest, day.date) : null,
          isPM: leaveRequest ? isDayType('half_day_pm', leaveRequest, day.date) : null
        });
      });
    }

    /**
     * Chooses the months that are to be selected by default
     */
    function setDefaultMonths () {
      vm.selectedMonths = [monthFromDate(moment()).index];
    }

    /**
     * It sets the properties of every day of the given month
     *
     * @param {Object} month
     * @return {Promise}
     */
    function setMonthDaysContactData (month) {
      return $q.all(month.days.map(function (day) {
        return $q.all(vm.contacts.map(function (contact) {
          return setDayContactData(day, contact.id);
        }));
      }));
    }

    /**
     * Sets the user's role based on his permissions
     */
    function setUserRole () {
      if (vm.roleOverride) {
        return $q.resolve().then(function () {
          userRole = vm.roleOverride;
        });
      } else {
        return $q.all([
          checkPermissions(sharedSettings.permissions.admin.administer),
          checkPermissions(sharedSettings.permissions.ssp.manage)
        ])
        .then(function (results) {
          userRole = results[0] ? 'admin' : (results[1] ? 'manager' : 'staff');
        });
      }
    }

    /**
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @param  {object} dateObj - Date UI object which handles look of a calendar cell
     * @return {object}
     */
    function styles (leaveRequest, dateObj) {
      var absenceType = _.find(vm.absenceTypes, function (absenceType) {
        return absenceType.id === leaveRequest.type_id;
      });

      return leaveRequest.balance_change > 0
        ? { borderColor: absenceType.color }
        : { borderColor: absenceType.color, backgroundColor: absenceType.color };
    }

    /**
     * Updates the given leave request in the calendar
     * For simplicity's sake, it directly deletes it and re-adds it
     *
     * @param  {Object} event
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function updateLeaveRequest (event, leaveRequest) {
      var oldLeaveRequest = leaveRequestFromIndexedList(leaveRequest);

      deleteLeaveRequest(null, oldLeaveRequest);
      addLeaveRequest(null, leaveRequest);
    }

    /**
     * Updates the properties of the days that the given leave request spans
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {Promise}
     */
    function updateLeaveRequestDaysContactData (leaveRequest) {
      return $q.all(leaveRequestDays(leaveRequest).map(function (day) {
        return setDayContactData(day, leaveRequest.contact_id, true);
      }));
    }
  }
});
