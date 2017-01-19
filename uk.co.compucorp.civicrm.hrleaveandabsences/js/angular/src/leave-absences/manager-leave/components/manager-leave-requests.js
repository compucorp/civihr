define([
  'common/lodash',
  'leave-absences/manager-leave/modules/components',
  'common/models/contact'
], function (_, components) {

  components.component('managerLeaveRequests', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave-requests.html';
    }],
    controllerAs: 'ctrl',
    controller: ['$log', '$q', '$rootScope', 'Contact', 'AbsencePeriod', 'AbsenceType', 'LeaveRequest',
      'OptionGroup', controller]
  });

  function controller($log, $q, $rootScope, Contact, AbsencePeriod, AbsenceType, LeaveRequest, OptionGroup) {
    "use strict";
    $log.debug('Component: manager-leave-requests');

    var vm = Object.create(this);

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.leaveRequestStatuses = [{
      name: 'all',
      label: 'All'
    }];
    vm.filters = {
      contact: {
        department: null,
        level_type: null,
        location: null,
        region: null
      },
      leaveRequest: {
        leaveStatus: vm.leaveRequestStatuses[0],
        pending_requests: false,
        contact: null,
        selectedPeriod: null,
        selectedAbsenceTypes: null
      }
    };
    vm.filteredUsers = [];
    vm.isFilterExpanded = false;
    //leaveRequests.table - to handle table data
    //leaveRequests.filter - to handle left nav filter data
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

    /**
     * Clears selected users and refreshes leave requests
     */
    vm.clearStaffSelection = function () {
      vm.filters.leaveRequest.contact = null;
      vm.refresh();
    };

    /**
     * Filters leave requests by status
     *
     * @param {Object} status - status object
     * @return {array}
     */
    vm.filterLeaveRequestByStatus = function (status) {
      if (status.name === 'all' || status === '') {
        return vm.leaveRequests.filter.list;
      }

      return vm.leaveRequests.filter.list.filter(function (request) {
        return request.status_id == status.value;
      });
    };

    /**
     * Returns the title of a Absence type when id is given
     *
     * @param {string} id - id of the Absence type
     * @return {string}
     */
    vm.getAbsenceTypesByID = function (id) {
      if (vm.absenceTypes && id) {
        var type = _.find(vm.absenceTypes, function (absenceType) {
            return absenceType.id === id;
          });

        return type ? type.title : null;
      }
    };

    /**
     * Returns the name(label) of a Leave request status when id is given
     *
     * @param {string} id - id of the leave request
     * @return {string}
     */
    vm.getLeaveStatusByValue = function (value) {
      var status = _.find(vm.leaveRequestStatuses, function (status) {
        return status.value == value;
      });

      return status ? status.label : null;
    };

    /**
     * Returns the class name for filter navigation when name is given
     *
     * @param {string} name - name of the status
     * @return {string}
     */
    vm.getNavBadge = function (name) {
      switch (name) {
        case 'approved':
          return 'badge-success';
        case 'rejected':
          return 'badge-danger';
        case 'cancelled':
        case 'all':
          return '';
        default:
          return 'badge-primary';
      }
    };

    /**
     * Returns the username when id is given
     *
     * @param {string} id - id of the user
     * @return {string}
     */
    vm.getUserNameByID = function (id) {
      var user = _.find(vm.filteredUsers, function (contact) {
        return contact.contact_id == id;
      });
      return user ? user.display_name : null;
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
     * Returns an array of a given size
     *
     * @param {number} n - no of elements in the array
     * @return {Array}
     */
    vm.getArrayOfSize = function (n) {
      return new Array(n || 0);
    };

    /**
     * Refreshes the leave request data
     *
     * @param {int} page - page number of the pagination element
     */
    vm.refresh = function (page) {
      if(page !== 0 || page <  vm.totalNoOfPages()) {
        vm.pagination.page = page > 0 ? page : 1;
        loadAllRequests();
      }
    };

    /**
     * Refreshes the leave request data and also changes current selected leave status
     *
     * @param {string} status - status to be selected
     */
    vm.refreshWithFilter = function (status) {
      vm.filters.leaveRequest.leaveStatus = status;
      vm.refresh();
    };

    /**
     * Calculates the total number of pages for the pagination
     *
     * @return {number}
     */
    vm.totalNoOfPages = function () {
      return Math.ceil(vm.leaveRequests.table.total / vm.pagination.size);
    };

    (function init() {
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
        loadAllRequests();
      });

      $rootScope.$on('LeaveRequest::updatedByManager', function () {
        vm.refresh(null, false);
      });
    })();

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    function contactFilters() {
      var filters = vm.filters.contact;

      return {
        department: filters.department ? filters.department.value : null,
        level_type: filters.level_type ? filters.level_type.value : null,
        location: filters.location ? filters.location.value : null,
        region: filters.region ? filters.region.value : null
      };
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
    function loadAbsenceTypes() {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }

    /**
     * Loads all requests
     *
     * @return {Promise}
     */
    function loadAllRequests() {
      vm.loading.content = true;
      Contact.all(contactFilters(), {
        page: 1,
        size: 0
      })
      .then(function (users) {
        vm.filteredUsers = users.list;
        $q.all([
          loadLeaveRequest('table'),
          loadLeaveRequest('filter')
        ])
        .then(function () {
          vm.loading.content = false;
        });
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
     * Loads all leave requests
     *
     * @param {string} type - load leave requests for the either the filter or the table
     * @return {Promise}
     */
    function loadLeaveRequest(type) {
      var filterByStatus = type !== 'filter',
        pagination = type === 'filter' ? {} : vm.pagination,
        returnFields = type === 'filter' ? {
            return: ['status_id']
          } : {};

      return LeaveRequest.all(leaveRequestFilters(filterByStatus), pagination, null, returnFields)
        .then(function (leaveRequests) {
          vm.leaveRequests[type] = leaveRequests;
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
     * Returns the filter object for leave request api
     *
     * @param {boolean} filterByStatus - if true then leave request api will be filtered using
     * selected leave request status in the left navigation bar, which would be used to show the
     * numbers of different statuses
     * @return {Object}
     */
    function leaveRequestFilters(filterByStatus) {
      var filters = vm.filters.leaveRequest;

      return {
        contact_id: prepareContactID(),
        managed_by: vm.contactId,
        status_id: prepareStatusFilter(filterByStatus),
        type_id: filters.selectedAbsenceTypes ? filters.selectedAbsenceTypes.id : null,
        from_date: {
          from: filters.selectedPeriod.start_date
        },
        to_date: {
          to: filters.selectedPeriod.end_date
        }
      };
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
     * Loads the status option values
     *
     * @return {Promise}
     */
    function loadStatuses() {
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
    function prepareContactID() {
      if (vm.filters.leaveRequest.contact) {
        return vm.filters.leaveRequest.contact.contact_id;
      }

      return vm.filteredUsers.length ? {
        "IN": vm.filteredUsers.map(function (contact) {
            return contact.contact_id;
          })
      } : null;
    }

    /**
     * Returns the status filter to be used for leave request api
     *
     * @param {boolean} filterByStatus - if true then leave request api will be filtered using
     * selected leave request status in the left navigation bar, which would be used to show the
     * numbers of different status's
     * @return {Object}
     */
    function prepareStatusFilter(filterByStatus) {
      var filters = vm.filters.leaveRequest,
        statusFilter = [],
        //get the value for the waiting_approval status
        waitingApprovalID = _.find(vm.leaveRequestStatuses, function (status) {
          return status.name === 'waiting_approval';
        }).value;

      //if filterByStatus is true then add the leaveStatus to be used in the leave request api
      if (filterByStatus && filters.leaveStatus && filters.leaveStatus.value) {
        statusFilter.push(filters.leaveStatus.value);
      }

      //if pending_requests is true then add the waiting_approval to be used in the leave request api
      if (filters.pending_requests && waitingApprovalID) {
        statusFilter.push(waitingApprovalID);
      }

      if (statusFilter.length) {
        return {
          "IN": statusFilter
        }
      }
    }

    return vm;
  }
});
