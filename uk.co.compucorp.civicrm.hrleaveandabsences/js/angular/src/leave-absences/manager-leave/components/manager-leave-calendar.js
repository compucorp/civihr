define([
  'common/lodash',
  'common/moment',
  'leave-absences/manager-leave/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl',
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
    controller: ['$controller', '$log', '$q', '$rootScope', 'Calendar', 'Contact', 'OptionGroup', 'LeaveRequest', controller]
  });

  function controller($controller, $log, $q, $rootScope, Calendar, Contact, OptionGroup, LeaveRequest) {
    $log.debug('Component: manager-leave-calendar');

    var parentCtrl = $controller('CalendarCtrl'),
      vm = Object.create(parentCtrl);

    /* In loadCalendar instead of updating vm.managedContacts on completion of each contact's promise.
     * Calendar data saved temporarily in tempContactData and once all the promises are resolved,
     * data is transferred to vm.managedContacts.
     * This is done so that browser paints the UI only once.
     */
    var tempContactData = [];

    vm.filteredContacts = [];
    vm.leaveRequests = [];
    vm.managedContacts = [];
    vm.filters = {
      contact: null,
      department: null,
      level_type: null,
      location: null,
      region: null,
      contacts_with_leaves: false
    };

    /**
     * Filters contacts if contacts_with_leaves is turned on
     *
     * @return {array}
     */
    vm.filterContacts = function () {
      if (vm.filters.contacts_with_leaves) {
        return vm.filteredContacts.filter(function (contact) {
          return (vm.leaveRequests[contact.id] && Object.keys(vm.leaveRequests[contact.id]).length > 0);
        });
      }

      return vm.filteredContacts;
    };

    /**
     * Returns the calendar information for a specific month
     *
     * @param  {int/string} contactID
     * @param  {object} monthObj
     * @return {array}
     */
    vm.getMonthData = function (contactID, monthObj) {
      var month;
      var contact = _.find(vm.managedContacts, function (contact) {
        return contact.id == contactID
      });

      if (contact && contact.calendarData) {
        month = _.find(contact.calendarData, function (month) {
          return (month.month === monthObj.month) && (month.year === monthObj.year);
        });

        return month ? month.data : [];
      }
    };

    /**
     * Refresh contacts and calendar data
     */
    vm.refresh = function () {
      vm.loading.calendar = true;
      vm._loadContacts()
        .then(function () {
          vm._loadLeaveRequestsAndCalender()
            .then(function () {
              vm.loading.calendar = false;
            });
        });
    };

    /**
     * Fetch all the months from the current period and
     * save it in vm.months
     */
    vm._fetchMonthsFromPeriod = function () {
      var months = [],
        startDate = moment(vm.selectedPeriod.start_date),
        endDate = moment(vm.selectedPeriod.end_date);

      while (startDate.isBefore(endDate)) {
        months.push({
          month: startDate.month(),
          year: startDate.year()
        });
        startDate.add(1, 'month');
      }

      vm.months = months;
    };

    /**
     * Index leave requests by contact_id as first level
     * and date as second level
     *
     * @param  {Array} leaveRequestsData - leave requests array from API
     */
    vm._indexLeaveRequests = function (leaveRequestsData) {
      _.each(leaveRequestsData, function (leaveRequest) {
        vm.leaveRequests[leaveRequest.contact_id] = vm.leaveRequests[leaveRequest.contact_id] || {};

        _.each(leaveRequest.dates, function (leaveRequestDate) {
          vm.leaveRequests[leaveRequest.contact_id][leaveRequestDate.date] = leaveRequest;
        });
      });
    };

    /**
     * Loads the calendar data
     *
     * @return {Promise}
     */
    vm._loadCalendar = function () {
      tempContactData = _.clone(vm.managedContacts);

      return $q.all(vm.managedContacts.map(function (contact, index) {
        return Calendar.get(contact.id, vm.selectedPeriod.id)
          .then(function (calendar) {
            tempContactData[index].calendarData = vm._setCalendarProps(vm.managedContacts[index].id, calendar);
          });
      }));
    };

    /**
     * Load all contacts with respect to filters
     *
     * @return {Promise}
     */
    vm._loadContacts = function () {
      return Contact.all(vm._prepareContactFilters(), {page: 1, size: 0})
        .then(function (contacts) {
          vm.filteredContacts = contacts.list;
        })
    };

    /**
     * Loads the leave request day types
     *
     * @return {Promise}
     */
    vm._loadDayTypes = function () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (dayTypesData) {
          vm._dayTypes = _.indexBy(dayTypesData, 'name');
        });
    };

    /**
     * Loads the leave requests and calendar
     *
     * @return {Promise}
     */
    vm._loadLeaveRequestsAndCalender = function () {
      return LeaveRequest.all({
        managed_by: vm.contactId,
        from_date: {
          from: vm.selectedPeriod.start_date
        },
        to_date: {
          to: vm.selectedPeriod.end_date
        }
      }, {}, null, null, false)
      .then(function (leaveRequestsData) {
        vm._indexLeaveRequests(leaveRequestsData.list);

        return vm._loadCalendar();
      })
      .then(function () {
        vm.managedContacts = tempContactData;
        vm._showMonthLoader();
      });
    };

    /**
     * Loads the managees of currently logged in user
     *
     * @return {Promise}
     */
    vm._loadManagees = function () {
      return Contact.find(vm.contactId)
        .then(function (contact) {
          contact.leaveManagees()
            .then(function (contacts) {
              vm.managedContacts = contacts;
              return vm._loadContacts();
            });
        });
    };

    /**
     * Loads the regions option values
     *
     * @return {Promise}
     */
    vm._loadRegions = function () {
      return OptionGroup.valuesOf('hrjc_region')
        .then(function (regions) {
          vm.regions = regions;
        });
    };

    /**
     * Loads the locations option values
     *
     * @return {Promise}
     */
    vm._loadLocations = function () {
      return OptionGroup.valuesOf('hrjc_location')
        .then(function (locations) {
          vm.locations = locations;
        });
    };

    /**
     * Loads the level types option values
     *
     * @return {Promise}
     */
    vm._loadLevelTypes = function () {
      return OptionGroup.valuesOf('hrjc_level_type')
        .then(function (levels) {
          vm.levelTypes = levels;
        });
    };

    /**
     * Loads the departments option values
     *
     * @return {Promise}
     */
    vm._loadDepartments = function () {
      return OptionGroup.valuesOf('hrjc_department')
        .then(function (departments) {
          vm.departments = departments;
        });
    };

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    vm._prepareContactFilters = function () {
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
    };

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     *
     * @param  {int/string} contactID
     * @param  {object} calendar
     * @return {object}
     */
    vm._setCalendarProps = function (contactID, calendar) {
      var leaveRequest,
        monthData = _.map(vm.months, function (month) {
          return _.extend(_.clone(month), {
            data: []
          })
        });

      _.each(calendar.days, function (dateObj) {
        //fetch leave request, first search by contact_id then by date
        leaveRequest = vm.leaveRequests[contactID] ? vm.leaveRequests[contactID][dateObj.date] : null;
        dateObj.UI = {
          isWeekend: calendar.isWeekend(vm._getDateObjectWithFormat(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(vm._getDateObjectWithFormat(dateObj.date)),
          isPublicHoliday: vm.isPublicHoliday(dateObj.date)
        };

        // set below props only if leaveRequest is found
        if (leaveRequest) {
          dateObj.leaveRequest = leaveRequest;
          dateObj.UI.styles = vm._getStyles(leaveRequest, dateObj);
          dateObj.UI.isRequested = vm._isPendingApproval(leaveRequest);
          dateObj.UI.isAM = vm._isDayType('half_day_am', leaveRequest, dateObj.date);
          dateObj.UI.isPM = vm._isDayType('half_day_pm', leaveRequest, dateObj.date);
        }

        vm._getMonthObjectByDate(moment(dateObj.date), monthData).data.push(dateObj);
      });

      return monthData;
    };

    (function init() {
      vm.loading.page = true;
      //Select current month as default
      vm.selectedMonths = [vm.monthLabels[moment().month()]];
      $q.all([
        vm._loadAbsencePeriods(),
        vm._loadAbsenceTypes(),
        vm._loadPublicHolidays(),
        vm._loadRegions(),
        vm._loadDepartments(),
        vm._loadLocations(),
        vm._loadLevelTypes(),
        vm._loadStatuses(),
        vm._loadDayTypes()
      ])
      .then(function () {
        return vm._loadManagees();
      })
      .then(function () {
        vm.legendCollapsed = false;
        return vm._loadLeaveRequestsAndCalender();
      })
      .finally(function () {
        vm.loading.page = false;
      });

      $rootScope.$on('LeaveRequest::updatedByManager', function () {
        vm.refresh();
      });
    })();

    return vm;
  }
});
