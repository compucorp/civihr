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
    var isAdmin = false; // updated on the init function after calling checkPermissions service
    var calendarData;

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

      loadContacts().then(function () {
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
        calendarData = calendars;
        // contacts are stored by index rather than by id, so it's necessary
        // to find the index of each contact by using their id
        var contactIds = tempContactData.map(function (contact) {
          return contact.id;
        });

        return $q.all(_.map(calendarData, function (calendar) {
          var index = contactIds.indexOf(calendar.contact_id);

          return vm._setCalendarProps(calendar.contact_id, calendar)
            .then(function (calendarProps) {
              tempContactData[index].calendarData = calendarProps;
            });
        }));
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
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     *
     * @param  {int/string} contactID
     * @param  {object} calendar
     * @return {object}
     */
    vm._setCalendarProps = function (contactID, calendar) {
      var monthData = _.map(vm.months, function (month) {
        return _.extend(_.clone(month), { data: [] });
      });

      return $q.all(_.map(calendar.days, function (dateObj) {
        return $q.all([
          calendar.isWeekend(vm._getDateObjectWithFormat(dateObj.date)),
          calendar.isNonWorkingDay(vm._getDateObjectWithFormat(dateObj.date))
        ])
        .then(function (results) {
          dateObj.UI = {
            isWeekend: results[0],
            isNonWorkingDay: results[1],
            isPublicHoliday: vm.isPublicHoliday(dateObj.date)
          };
        })
        .then(function () {
          // fetch leave request, first search by contact_id then by date
          var leaveRequest = vm.leaveRequests[contactID] ? vm.leaveRequests[contactID][dateObj.date] : null;

          if (leaveRequest) {
            dateObj.UI.styles = vm._getStyles(leaveRequest, dateObj);
            dateObj.UI.isRequested = vm._isPendingApproval(leaveRequest);
            dateObj.UI.isAM = vm._isDayType('half_day_am', leaveRequest, dateObj.date);
            dateObj.UI.isPM = vm._isDayType('half_day_pm', leaveRequest, dateObj.date);
          }
        })
        .then(function () {
          vm._getMonthObjectByDate(moment(dateObj.date), monthData).data.push(dateObj);
        });
      }))
      .then(function () {
        return monthData;
      });
    };

    (function init () {
      checkPermissions(sharedSettings.permissions.admin.administer)
      .then(function (_isAdmin_) {
        isAdmin = _isAdmin_;

        vm._init(function () {
          return $q.all([
            loadOptionValues(),
            loadManagees()
          ]);
        });
      });

      $rootScope.$on('LeaveRequest::updatedByManager', vm.refresh);
      $rootScope.$on('LeaveRequest::deleted', deleteLeaveRequest);
    })();

    /**
     * Event handler for Delete event of Leave Request
     *
     * @param  {object} event
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function deleteLeaveRequest (event, leaveRequest) {
      var contactID = leaveRequest.contact_id;
      // Find the calendarData for the deleted requests user
      var calendar = _.find(calendarData, function (calendar) {
        return calendar.contact_id === contactID;
      });

      vm.leaveRequests[contactID] = _.omit(vm.leaveRequests[contactID], function (leaveRequestObj) {
        return leaveRequestObj.id === leaveRequest.id;
      });
      vm._resetMonths();
      vm._setCalendarProps(contactID, calendar);
    }

    /**
     * Load all contacts with respect to filters
     *
     * @return {Promise}
     */
    function loadContacts () {
      return Contact.all(prepareContactFilters(), null, 'display_name')
        .then(function (contacts) {
          vm.filteredContacts = contacts.list;
        });
    }

    /**
     * Loads the managees of currently logged in user
     *
     * @return {Promise}
     */
    function loadManagees () {
      if (isAdmin) {
        return Contact.all()
          .then(function (contacts) {
            vm.managedContacts = contacts.list;
            vm.filteredContacts = contacts.list;
          });
      }

      return Contact.find(vm.contactId)
        .then(function (contact) {
          return contact.leaveManagees();
        })
        .then(function (contacts) {
          vm.managedContacts = contacts;
        })
        .then(function () {
          return loadContacts();
        });
    }

    /**
     * Loads the OptionValues necessary for the controller
     *
     * @return {Promise}
     */
    function loadOptionValues () {
      return OptionGroup.valuesOf([
        'hrjc_region',
        'hrjc_location',
        'hrjc_level_type',
        'hrjc_department'
      ])
      .then(function (data) {
        vm.regions = data.hrjc_region;
        vm.locations = data.hrjc_location;
        vm.levelTypes = data.hrjc_level_type;
        vm.departments = data.hrjc_department;
      });
    }

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    function prepareContactFilters () {
      return {
        department: vm.filters.department ? vm.filters.department.value : null,
        level_type: vm.filters.level_type ? vm.filters.level_type.value : null,
        location: vm.filters.location ? vm.filters.location.value : null,
        region: vm.filters.region ? vm.filters.region.value : null,
        id: {
          'IN': vm.filters.contact
            ? [vm.filters.contact.id]
            : vm.managedContacts.map(function (contact) {
              return contact.id;
            })
        }
      };
    }

    return vm;
  }
});
