define([
  'common/lodash',
  'common/moment',
  'leave-absences/manager-leave/modules/components',
  'common/models/contact',
], function (_, moment, components) {

  components.component('managerLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$log', '$q', 'shared-settings', 'OptionGroup', 'Contact', 'AbsencePeriod', 'AbsenceType',
      'Calendar', 'LeaveRequest', 'PublicHoliday', controller]
  });


  function controller(
    $log, $q, sharedSettings, OptionGroup, Contact, AbsencePeriod, AbsenceType,
    Calendar, LeaveRequest, PublicHoliday) {
    $log.debug('Component: manager-leave-calendar');

    var dayTypes = [],
      publicHolidays = [],
      leaveRequests = [],
      leaveRequestStatuses = [],
      vm = Object.create(this);

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.filteredContacts = [];
    vm.managedContacts = [];
    vm.filters = {
      contact: null,
      department: null,
      level_type: null,
      location: null,
      region: null,
      contacts_with_leaves: false
    };
    vm.loading = {
      calendar: false,
      page: false
    };
    vm.months = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];
    vm.selectedMonths = [];
    vm.selectedPeriod = null;

    /**
     * Filters contacts if contacts_with_leaves is turned on
     *
     * @return {array}
     */
    vm.filterContacts = function () {
      if(vm.filters.contacts_with_leaves) {
        return vm.filteredContacts.filter(function (contact) {
          return leaveRequests.find(function (request) {
            return request.contact_id == contact.id;
          });
        });
      }

      return vm.filteredContacts;
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
     * @param  {int/string} contactID
     * @param  {int} month
     * @return {array}
     */
    vm.getMonthData = function (contactID, month) {
      var contact = _.find(vm.managedContacts, function (contact) {
        return contact.id == contactID
      });

      if (contact && contact.calendarData) {
        var calendarData = contact.calendarData;
        var datesForTheMonth = [],
          dates = Object.keys(calendarData.days);

        dates.forEach(function (date) {
          if (moment(parseInt(date)).month() === month) {
            datesForTheMonth.push(calendarData.days[date]);
          }
        });

        return datesForTheMonth;
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
     * Refresh contacts and calendar data
     */
    vm.refresh = function () {
      vm.loading.calendar = true;
      loadContacts()
        .then(function () {
          loadLeaveRequestsAndCalender()
            .then(function () {
              vm.loading.calendar = false;
            });
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
        loadRegions(),
        loadDepartments(),
        loadLocations(),
        loadLevelTypes(),
        loadStatuses(),
        loadDayTypes()
      ])
      .then(function () {
        return loadManagees();
      })
      .then(function () {
        vm.legendCollapsed = false;
        return loadLeaveRequestsAndCalender();
      })
      .finally(function () {
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
      return moment(date, sharedSettings.serverDateFormat).clone();
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
      var promises = [];
      _.each(vm.managedContacts, function (contact, index) {
        promises.push(Calendar.get(contact.id, vm.selectedPeriod.id)
          .then(function (calendar) {
            var defer = $q.defer();
            var worker = new Worker(location.origin +
              '/sites/all/modules/civicrm/tools/extensions/civihr/uk.co.compucorp.civicrm.hrleaveandabsences/' +
              'js/angular/src/leave-absences/manager-leave/components/worker.js');

            //Send all variables to the web worker context
            worker.postMessage({
              command: 'setValue',
              variables: {
                calendar: calendar,
                contactID: vm.managedContacts[index].id,
                leaveRequests: leaveRequests,
                publicHolidays: publicHolidays,
                leaveRequestStatuses: leaveRequestStatuses,
                absenceTypes: vm.absenceTypes,
                dayTypes: dayTypes,
                sharedSettings: sharedSettings
              }
            });

            //Start web worker After setting all variables
            worker.postMessage({
              command: 'start'
            });

            //Event listener for messages sent from web worker
            worker.onmessage = function (e) {
              if(!e.data.error) {
                vm.managedContacts[index].calendarData = JSON.parse(e.data.calendar);
              }
              defer.resolve();
              worker.terminate();
            };

            return defer.promise;

          }));
      });

      return $q.all(promises);
    }

    /**
     * Load all contacts with respect to filters
     *
     * @return {Promise}
     */
    function loadContacts() {
      return Contact.all(prepareContactFilters(), {page: 1, size: 0})
        .then(function (contacts) {
          vm.filteredContacts = contacts.list;
        })
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
     * Loads the leave requests and calendar
     *
     * @return {Promise}
     */
    function loadLeaveRequestsAndCalender() {
      return LeaveRequest.all({
        managed_by: vm.contactId,
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
     * Loads the managees of currently logged in user
     *
     * @return {Promise}
     */
    function loadManagees() {
      return Contact.find(vm.contactId)
        .then(function (contact) {
          contact.leaveManagees()
            .then(function (contacts) {
              vm.managedContacts = contacts;
              return loadContacts();
            });
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
     * Loads the regions option values
     *
     * @return {Promise}
     */
    function loadRegions() {
      return OptionGroup.valuesOf('hrjc_region')
        .then(function (regions) {
          vm.regions = regions;
        });
    }

    /**
     * Loads the locations option values
     *
     * @return {Promise}
     */
    function loadLocations() {
      return OptionGroup.valuesOf('hrjc_location')
        .then(function (locations) {
          vm.locations = locations;
        });
    }

    /**
     * Loads the level types option values
     *
     * @return {Promise}
     */
    function loadLevelTypes() {
      return OptionGroup.valuesOf('hrjc_level_type')
        .then(function (levels) {
          vm.levelTypes = levels;
        });
    }

    /**
     * Loads the departments option values
     *
     * @return {Promise}
     */
    function loadDepartments() {
      return OptionGroup.valuesOf('hrjc_department')
        .then(function (departments) {
          vm.departments = departments;
        });
    }

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    function prepareContactFilters() {
      var filters = vm.filters;

      return {
        id: {
          "IN": vm.filters.contact ? [vm.filters.contact.id] :
            vm.managedContacts.map(function (contact) {
              return contact.id;
            })
        },
        department: filters.department ? filters.department.value : null,
        level_type: filters.level_type ? filters.level_type.value : null,
        location: filters.location ? filters.location.value : null,
        region: filters.region ? filters.region.value : null
      };
    }

    return vm;
  }
});
