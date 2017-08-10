/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/components',
  'common/models/contact'
], function (_, components) {
  components.component('manageLeaveRequests', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/manage-leave-requests.html';
    }],
    controllerAs: 'vm',
    controller: ManageLeaveRequestsController
  });

  ManageLeaveRequestsController.$inject = [
    '$log', '$q', '$rootScope', 'Contact', 'checkPermissions', 'OptionGroup',
    'shared-settings', 'AbsencePeriod', 'AbsenceType', 'LeaveRequest'
  ];

  function ManageLeaveRequestsController ($log, $q, $rootScope, Contact, checkPermissions, OptionGroup, sharedSettings, AbsencePeriod, AbsenceType, LeaveRequest) {
    'use strict';
    $log.debug('Component: manage-leave-requests');

    var vm = this;
    var filterByAll = { name: 'all', label: 'All' };

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.filteredUsers = [];
    vm.isFilterExpanded = false;
    vm.isAdmin = false; // this property is updated on controller initialization
    vm.leaveRequestStatuses = [filterByAll];
    vm.filters = {
      contact: {
        department: null,
        level_type: null,
        location: null,
        region: null
      },
      leaveRequest: {
        leaveStatus: vm.leaveRequestStatuses[0],
        pending_requests: true,
        contact_id: null,
        selectedPeriod: null,
        selectedAbsenceTypes: null,
        assignedTo: 'me'
      }
    };
    vm.filtersByAssignee = [
      { type: 'me', label: 'Assigned To Me' },
      { type: 'unassigned', label: 'Unassigned' },
      { type: 'all', label: 'All' }
    ];
    // leaveRequests.table - to handle table data
    // leaveRequests.filter - to handle left nav filter data
    vm.leaveRequests = {
      table: {
        list: []
      },
      filter: {
        list: []
      }
    };
    vm.loading = {
      content: true,
      page: true
    };
    vm.pagination = {
      page: 1,
      size: 7
    };

    vm.clearStaffSelection = clearStaffSelection;
    vm.filterLeaveRequestByStatus = filterLeaveRequestByStatus;
    vm.getAbsenceTypesByID = getAbsenceTypesByID;
    vm.getArrayOfSize = getArrayOfSize;
    vm.getLeaveStatusByValue = getLeaveStatusByValue;
    vm.getNavBadge = getNavBadge;
    vm.getUserNameByID = getUserNameByID;
    vm.labelPeriod = labelPeriod;
    vm.refresh = refresh;
    vm.refreshWithFilter = refreshWithFilter;
    vm.refreshWithFilterByAssignee = refreshWithFilterByAssignee;
    vm.totalNoOfPages = totalNoOfPages;

    (function init () {
      checkPermissions(sharedSettings.permissions.admin.administer)
        .then(function (isAdmin) {
          vm.isAdmin = isAdmin;

          $q.all([
            loadAbsencePeriods(),
            loadAbsenceTypes(),
            loadRegions(),
            loadDepartments(),
            loadLocations(),
            loadLevelTypes(),
            loadStatuses()
          ])
            .then(function () {
              vm.loading.page = false;
              loadManageesAndLeaves();
            });

          registerEvents();
        });
    })();

    /**
     * Clears selected users and refreshes leave requests
     */
    function clearStaffSelection () {
      vm.filters.leaveRequest.contact_id = null;
      vm.refresh();
    }

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    function contactFilters () {
      var filters = vm.filters.contact;

      return {
        department: filters.department,
        level_type: filters.level_type,
        location: filters.location,
        region: filters.region
      };
    }

    /**
     * Filters leave requests by status
     *
     * @param {Object} status - status object
     * @return {array}
     */
    function filterLeaveRequestByStatus (status) {
      if (status.name === 'all' || status === '') {
        return vm.leaveRequests.filter.list;
      }

      return vm.leaveRequests.filter.list.filter(function (request) {
        return request.status_id === status.value;
      });
    }

    /**
     * Returns the title of a Absence type when id is given
     *
     * @param {string} id - id of the Absence type
     * @return {string}
     */
    function getAbsenceTypesByID (id) {
      if (vm.absenceTypes && id) {
        var type = _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id === id;
        });

        return type ? type.title : null;
      }
    }

    /**
     * Returns an array of a given size
     *
     * @param {number} n - no of elements in the array
     * @return {Array}
     */
    function getArrayOfSize (n) {
      return new Array(n || 0);
    }

    /**
     * Returns the name(label) of a Leave request status when id is given
     *
     * @param {string} id - id of the leave request
     * @return {string}
     */
    function getLeaveStatusByValue (value) {
      var status = _.find(vm.leaveRequestStatuses, function (status) {
        return status.value === value;
      });

      return status ? status.label : null;
    }

    /**
     * Returns the class name for filter navigation when name is given
     *
     * @param {string} name - name of the status
     * @return {string}
     */
    function getNavBadge (name) {
      switch (name) {
        case sharedSettings.statusNames.approved:
          return 'badge-success';
        case sharedSettings.statusNames.rejected:
          return 'badge-danger';
        case sharedSettings.statusNames.cancelled:
        case 'all':
          return '';
        default:
          return 'badge-primary';
      }
    }

    /**
     * Returns status value for the given name
     * @param {String} statusName
     * @return {String}
     */
    function getStatusValueFromName (statusName) {
      return _.find(vm.leaveRequestStatuses, function (status) {
        return status.name === statusName;
      }).value;
    }

    /**
     * Returns the username when id is given
     *
     * @param {string} id - id of the user
     * @return {string}
     */
    function getUserNameByID (id) {
      var user = _.find(vm.filteredUsers, function (contact) {
        return contact.id === id;
      });
      return user ? user.display_name : null;
    }

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {AbsencePeriodInstance} period
     * @return {string}
     */
    function labelPeriod (period) {
      return period.current ? 'Current Period (' + period.title + ')' : period.title;
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
          vm.filters.leaveRequest.selectedPeriod = _.find(vm.absencePeriods, function (period) {
            return !!period.current;
          });
        });
    }

    /**
     * Loads the absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }

    /**
     * Loads the managees and calls loadLeaveRequests()
     *
     * @return {Promise}
     */
    function loadManageesAndLeaves () {
      vm.loading.content = true;

      return (vm.isAdmin ? Contact.all(contactFilters()) : Contact.leaveManagees(vm.contactId, contactFilters()))
        .then(function (users) {
          vm.filteredUsers = vm.isAdmin ? users.list : users;

          return $q.all([
            loadLeaveRequests('table'),
            loadLeaveRequests('filter')
          ]);
        })
        .then(function () {
          /*
           * If the status filter is not set to "All" and
           * there are no requests loaded, then
           * the status filter is set to "All" and the controller is refreshed
           */
          if (vm.filters.leaveRequest.leaveStatus !== filterByAll && vm.leaveRequests.table.list.length === 0) {
            vm.filters.leaveRequest.leaveStatus = filterByAll;

            vm.refresh();

            return;
          }

          vm.loading.content = false;
        });
    }

    /**
     * Loads the departments option values
     *
     * @return {Promise}
     */
    function loadDepartments () {
      return OptionGroup.valuesOf('hrjc_department')
        .then(function (departments) {
          vm.departments = departments;
        });
    }

    /**
     * Loads all leave requests
     *
     * @param {string} type - load leave requests for the either the filter or the table
     * @return {Promise}
     */
    function loadLeaveRequests (type) {
      var filterByStatus = type !== 'filter';
      // {pagination: {size:0}} - Load all requests instead of 25
      var pagination = type === 'filter' ? { size: 0 } : vm.pagination;
      var returnFields = type === 'filter' ? {
        return: ['status_id']
      } : {};

      vm.leaveRequests[type].list = []; // flushes the current cached data
      // cache is set to always false as changing selection either in status menu
      // or pages or adding new requests was reverting back to older cache
      return LeaveRequest.all(leaveRequestFilters(filterByStatus), pagination, 'from_date DESC', returnFields, false)
        .then(function (leaveRequests) {
          vm.leaveRequests[type] = leaveRequests;
        });
    }

    /**
     * Loads the level types option values
     *
     * @return {Promise}
     */
    function loadLevelTypes () {
      return OptionGroup.valuesOf('hrjc_level_type')
        .then(function (levels) {
          vm.levelTypes = levels;
        });
    }

    /**
     * Loads the locations option values
     *
     * @return {Promise}
     */
    function loadLocations () {
      return OptionGroup.valuesOf('hrjc_location')
        .then(function (locations) {
          vm.locations = locations;
        });
    }

    /**
     * Returns the filter object for leave request api
     *
     * @param {boolean} filterByStatus - if true then leave request api will be filtered using
     * selected leave request status in the left navigation bar, which would be used to show the
     * numbers of different statuses
     * @return {Object}
     */
    function leaveRequestFilters (filterByStatus) {
      var filters = vm.filters.leaveRequest;

      return {
        contact_id: prepareContactID(),
        managed_by: (vm.isAdmin && filters.assignedTo !== 'me' ? undefined : vm.contactId),
        status_id: prepareStatusFilter(filterByStatus),
        type_id: filters.selectedAbsenceTypes ? filters.selectedAbsenceTypes.id : null,
        from_date: {
          from: filters.selectedPeriod.start_date
        },
        to_date: {
          to: filters.selectedPeriod.end_date
        },
        unassigned: (filters.assignedTo === 'unassigned' ? true : undefined)
      };
    }

    /**
     * Loads the regions option values
     *
     * @return {Promise}
     */
    function loadRegions () {
      return OptionGroup.valuesOf('hrjc_region')
        .then(function (regions) {
          vm.regions = regions;
        });
    }

    /**
     * Loads the status option values
     *
     * @return {Promise}
     */
    function loadStatuses () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          vm.leaveRequestStatuses = statuses.concat(vm.leaveRequestStatuses);
        });
    }

    /**
     * Returns the contact ID to be used for leave request api
     *
     * @return {Object}
     */
    function prepareContactID () {
      // If there is no users after applying filter, the selected contact_id
      // should not be sent to the leave request API, as it will still load
      // the leave requests for the selected contact id
      if (vm.filteredUsers.length > 0 && vm.filters.leaveRequest.contact_id) {
        return vm.filters.leaveRequest.contact_id;
      }

      return {
        'IN': vm.filteredUsers.map(function (contact) {
          return contact.id;
        })
      };
    }

    /**
     * Applies the filters when pending request is checked
     *
     * @param {Array} statusFilter
     * @return {Array}
     */
    function preparePendingRequestOnlyFilters (statusFilter) {
      var pendingRequestFilters = [
        getStatusValueFromName(sharedSettings.statusNames.moreInformationRequired),
        getStatusValueFromName(sharedSettings.statusNames.awaitingApproval)
      ];

      // Remove others filters
      statusFilter = statusFilter.filter(function (status) {
        return pendingRequestFilters.indexOf(status) > -1;
      });

      // If statusFilter still has items, means one of pendingRequestFilters is selected on the UI
      // Then do not add new filters
      if (statusFilter.length === 0) {
        // Add pending request specific filters
        statusFilter = statusFilter.concat(pendingRequestFilters);
      }

      return statusFilter;
    }

    /**
     * Returns the status filter to be used for leave request api
     *
     * @param {boolean} filterByStatus - if true then leave request api will be filtered using
     * selected leave request status in the left navigation bar, which would be used to show the
     * numbers of different status's
     * @return {Object}
     */
    function prepareStatusFilter (filterByStatus) {
      var filters = vm.filters.leaveRequest;
      var statusFilter = [];

      // if filterByStatus is true then add the leaveStatus to be used in the leave request api
      if (filterByStatus && filters.leaveStatus && filters.leaveStatus.value) {
        statusFilter.push(filters.leaveStatus.value);
      }

      if (filters.pending_requests) {
        statusFilter = preparePendingRequestOnlyFilters(statusFilter);
      }

      if (statusFilter.length) {
        return {
          'IN': statusFilter
        };
      }
    }

    /**
     * Refreshes the leave request data
     *
     * @param {int} page - page number of the pagination element
     */
    function refresh (page) {
      // vm.refresh is called from registerEvents and was sending events object in the function "arguments".
      // Without the check parameter page was set to the passed event object from function "arguments" and
      // hence the page was not getting refreshed as the below condition would always fail.
      page = typeof (page) === 'number' ? page : 1;

      // page <= vm.totalNoOfPages() - Do not load new data if the page no is more than total
      // no of pages, this can happen when Next button is pressed on the pagination
      // vm.totalNoOfPages() === 0 - If total no of pages is 0 then load new data
      // This can happen when the list is empty and a new filter is applied
      if (page <= vm.totalNoOfPages() || vm.totalNoOfPages() === 0) {
        vm.pagination.page = page;

        loadManageesAndLeaves();
      }
    }

    /**
     * Refreshes the leave request data and also changes current selected leave status
     *
     * @param {string} status - status to be selected
     */
    function refreshWithFilter (status) {
      vm.filters.leaveRequest.leaveStatus = status;
      vm.refresh();
    }

    /**
     * Refreshes the leave request data and also changes current selected leave status
     *
     * @param {string} type - by assignee type to be selected
     */
    function refreshWithFilterByAssignee (type) {
      vm.filters.leaveRequest.assignedTo = type;
      vm.refresh();
    }

    /**
     * Register events which will be called by other modules
     */
    function registerEvents () {
      $rootScope.$on('LeaveRequest::updatedByManager', function () { vm.refresh(); });
      $rootScope.$on('LeaveRequest::new', function () { vm.refresh(); });
      $rootScope.$on('LeaveRequest::edit', function () { vm.refresh(); });
      $rootScope.$on('LeaveRequest::deleted', function () { vm.refresh(); });
    }

    /**
     * Calculates the total number of pages for the pagination
     *
     * @return {number}
     */
    function totalNoOfPages () {
      return Math.ceil(vm.leaveRequests.table.total / vm.pagination.size);
    }
  }
});
