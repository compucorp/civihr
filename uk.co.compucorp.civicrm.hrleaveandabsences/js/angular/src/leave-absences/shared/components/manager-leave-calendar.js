/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl',
  'common/models/contact'
], function (_, moment, components) {
  components.component('managerLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/manager-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: [
      '$controller', '$log', '$q', '$rootScope', 'shared-settings', 'checkPermissions',
      'Calendar', 'Contact', 'OptionGroup', controller]
  });

  function controller ($controller, $log, $q, $rootScope, sharedSettings, checkPermissions, Calendar, Contact, OptionGroup) {
    $log.debug('Component: manager-leave-calendar');

    var parentCtrl = $controller('CalendarCtrl');
    var vm = Object.create(parentCtrl);
    var isAdmin = false;

    /* In loadCalendar instead of updating vm.managedContacts on completion of each contact's promise.
     * Calendar data saved temporarily in tempContactData and once all the promises are resolved,
     * data is transferred to vm.managedContacts.
     * This is done so that browser paints the UI only once.
     */
    var tempContactData = [];

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
        return +contact.id === +contactID;
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
      vm._resetMonths();
      vm._loadContacts()
        .then(function () {
          vm._loadLeaveRequestsAndCalendar();
        });
    };

    /**
     * Returns skeleton for the month object
     *
     * @param  {Object} startDate
     * @return {Object}
     */
    vm._getMonthSkeleton = function (startDate) {
      return {
        month: startDate.month(),
        year: startDate.year()
      };
    };

    /**
     * Index leave requests by contact_id as first level
     * and date as second level
     *
     * @param  {Array} leaveRequests - leave requests array from API
     */
    vm._indexLeaveRequests = function (leaveRequests) {
      vm.leaveRequests = {};

      _.each(leaveRequests, function (leaveRequest) {
        vm.leaveRequests[leaveRequest.contact_id] = vm.leaveRequests[leaveRequest.contact_id] || {};

        _.each(leaveRequest.dates, function (leaveRequestDate) {
          vm.leaveRequests[leaveRequest.contact_id][leaveRequestDate.date] = leaveRequest;
        });
      });
    };

    /**
     * Loads the calendar data for each contact
     *
     * @return {Promise}
     */
    vm._loadCalendar = function () {
      tempContactData = _.clone(vm.managedContacts);

      return Calendar.get(vm.managedContacts.map(function (contact) {
        return contact.id;
      }), vm.selectedPeriod.id)
      .then(function (calendars) {
        // contacts are stored by index rather than by id, so it's necessary
        // to find the index of each contact by using their id
        var contactIds = tempContactData.map(function (contact) {
          return contact.id;
        });

        calendars.forEach(function (calendar) {
          var index = contactIds.indexOf(calendar.contact_id);

          tempContactData[index].calendarData = vm._setCalendarProps(calendar.contact_id, calendar);
        });
      });
    };

    /**
     * Load all contacts with respect to filters
     *
     * @return {Promise}
     */
    vm._loadContacts = function () {
      return Contact.all(vm._prepareContactFilters(), {page: 1, size: 0}, 'display_name')
        .then(function (contacts) {
          vm.filteredContacts = contacts.list;
        });
    };

    /**
     * Loads the leave requests and calendar
     *
     * @return {Promise}
     */
    vm._loadLeaveRequestsAndCalendar = function () {
      return parentCtrl._loadLeaveRequestsAndCalendar.call(vm, 'managed_by', false, function () {
        vm.managedContacts = tempContactData;
      });
    };

    /**
     * Loads the managees of currently logged in user
     *
     * @return {Promise}
     */
    vm._loadManagees = function () {
      if (isAdmin) {
        return Contact.all()
          .then(function (contacts) {
            vm.managedContacts = contacts.list;
            vm.filteredContacts = contacts.list;
          });
      }

      return Contact.find(vm.contactId)
        .then(function (contact) {
          return contact.leaveManagees()
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
          'IN': vm.filters.contact ? [vm.filters.contact.id]
            : vm.managedContacts.map(function (contact) {
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
      var leaveRequest;
      var monthData = _.map(vm.months, function (month) {
        return _.extend(_.clone(month), {
          data: []
        });
      });

      _.each(calendar.days, function (dateObj) {
        // fetch leave request, first search by contact_id then by date
        leaveRequest = vm.leaveRequests[contactID] ? vm.leaveRequests[contactID][dateObj.date] : null;
        dateObj.UI = {
          isWeekend: calendar.isWeekend(vm._getDateObjectWithFormat(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(vm._getDateObjectWithFormat(dateObj.date)),
          isPublicHoliday: vm.isPublicHoliday(dateObj.date)
        };

        // set below props only if leaveRequest is found
        if (leaveRequest) {
          dateObj.UI.styles = vm._getStyles(leaveRequest, dateObj);
          dateObj.UI.isRequested = vm._isPendingApproval(leaveRequest);
          dateObj.UI.isAM = vm._isDayType('half_day_am', leaveRequest, dateObj.date);
          dateObj.UI.isPM = vm._isDayType('half_day_pm', leaveRequest, dateObj.date);
        }

        vm._getMonthObjectByDate(moment(dateObj.date), monthData).data.push(dateObj);
      });

      return monthData;
    };

    (function init () {
      checkPermissions(sharedSettings.permissions.admin.administer)
      .then(function (_isAdmin_) {
        isAdmin = _isAdmin_;

        vm._init(function () {
          return $q.all([
            vm._loadRegions(),
            vm._loadDepartments(),
            vm._loadLocations(),
            vm._loadLevelTypes()
          ])
            .then(function () {
              return vm._loadManagees();
            });
        });
      });
      $rootScope.$on('LeaveRequest::updatedByManager', vm.refresh);
    })();

    return vm;
  }
});
