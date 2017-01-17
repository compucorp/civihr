define([
  'leave-absences/manager-leave/modules/components',
  'leave-absences/shared/models/leave-request-model',
  'common/models/contact',
], function (components) {

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
    $log.debug('Component: manager-leave-requests');

    var vm = Object.create(this),
      allStatus = {
        name: 'all',
        label: 'All'
      };

    vm.isFilterExpanded = false;
    vm.absencePeriods = [];
    vm.filteredUsers = [];
    vm.absenceTypes = [];
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
    vm.filters = {
      contactFilters: {
        region: null,
        department: null,
        level_type: [],
        location: null
      },
      leaveRequestFilters: {
        selectedUsers: null,
        selectedPeriod: null,
        selectedAbsenceTypes: null,
        leaveStatus: allStatus,
        pending_requests: false
      }
    };
    vm.pagination = {
      page: 1,
      size: 7
    };
    vm.loading = {
      content: true,
      page: true
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
     * Returns the label of a Leave request when id is given
     *
     * @param {string} id - id of the leave request
     * @return {string}
     */
    vm.getLeaveStatusByValue = function (id) {
      if (vm.leaveRequestStatuses.length) {
        return vm.leaveRequestStatuses.find(function (status) {
          return status.value == id;
        }).label;
      }
    };

    /**
     * Returns the title of a Absence type when id is given
     *
     * @param {string} id - id of the Absence type
     * @return {string}
     */
    vm.getAbsenceTypesByID = function (id) {
      if (vm.absenceTypes && id) {
        var type,
          i;
        for (i in vm.absenceTypes) {
          type = vm.absenceTypes[i];
          if (type.id == id) {
            return type.title;
          }
        }
      }
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
      })
    };

    /**
     * Returns the class name for filter navigation when name is given
     *
     * @param {string} name - name of the status
     * @return {string}
     */
    vm.getNavBadge = function (name) {
      if (name === 'approved') {
        return 'badge-success';
      } else if (name === 'rejected') {
        return 'badge-danger';
      } else if (name === 'cancelled' || name === 'all') {
        return '';
      } else {
        return 'badge-primary';
      }
    };

    /**
     * Returns an array of a given size
     *
     * @param {number} n - no of elements in the array
     * @return {Array}
     */
    vm.range = function (n) {
      if (n) {
        return new Array(n);
      }
    };

    /**
     * Calculates the total number of pages for the pagination
     *
     * @return {number}
     */
    vm.totalNoOfPages = function () {
      return Math.ceil(vm.leaveRequests.table.total / vm.pagination.size);
    };

    /**
     * Loads the next page for pagination element based on current page no
     */
    vm.nextPage = function () {
      if (vm.pagination.page < vm.totalNoOfPages()) {
        vm.refresh(++vm.pagination.page);
      }
    };

    /**
     * Refreshes the leave request data
     *
     * @param {string} page - page number of the pagination element
     */
    vm.refresh = function (page, cache) {
      page = page ? page : 1;
      loadAllRequests(page, cache);
    };

    /**
     * Refreshes the leave request data and also changes current selected leave status
     *
     * @param {string} status - status to be selected
     */
    vm.refreshWithFilter = function (status) {
      vm.filters.leaveRequestFilters.leaveStatus = status;
      vm.refresh();
    };

    /**
     * Returns the username when id is given
     *
     * @param {string} id - id of the user
     * @return {string}
     */
    vm.getUserNameByID = function (id) {
      var user = vm.filteredUsers.find(function (data) {
        return data.contact_id == id;
      });
      return user ? user.display_name : null;
    };

    /**
     * Clears selected users and refreshes leave requests
     */
    vm.clearStaffSelection = function () {
      vm.filters.leaveRequestFilters.selectedUsers = null;
      vm.refresh();
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
          loadAllRequests(1);
        })

        $rootScope.$on('LeaveRequest::updatedByManager', function () {
          vm.refresh(null, false);
        });
    })();

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods() {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          vm.absencePeriods = absencePeriods;
          vm.filters.leaveRequestFilters.selectedPeriod = _.find(vm.absencePeriods, function (period) {
            return period.current === true;
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
    function loadAllRequests(page, cache) {
      vm.pagination.page = page;
      vm.loading.content = true;
      Contact.all(contactFilters(), {
        page: 1,
        size: 0
      })
        .then(function (data) {
          vm.filteredUsers = data.list;

          $q.all([
            loadLeaveRequest('table', cache),
            loadLeaveRequest('filter', cache)
          ])
          .then(function () {
            vm.loading.content = false;
          })
        });
    }

    /**
     * Loads all leave requests
     *
     * @param {string} type - load leave requests for the either the filter or the table
     * @return {Promise}
     */
     function loadLeaveRequest(type, cache) {
       var filterByStatus = type !== 'filter',
         pagination = type === 'filter' ? {} : vm.pagination;

       return LeaveRequest.all(leaveRequestFilters(filterByStatus), pagination, null, null, cache)
        .then(function (leaveRequests) {
          vm.leaveRequests[type] = leaveRequests;
        });
    }

    /**
     * Returns the filter object for leave request api
     *
     * @param {boolean} filterByStatus - if true then leave request api will be filtered using
     * selected leave request status in the left navigation bar, which would be used to show the
     * numbers of different status's
     * @return {Object}
     */
    function leaveRequestFilters(filterByStatus) {
      var filters = vm.filters.leaveRequestFilters;

      return {
        managed_by: vm.contactId,
        type_id: filters.selectedAbsenceTypes ? filters.selectedAbsenceTypes.id : null,
        status_id: prepareStatusFilter(filterByStatus),
        from_date: {
          from: filters.selectedPeriod.start_date
        },
        to_date: {
          to: filters.selectedPeriod.end_date
        },
        contact_id: prepareContactID()
      };
    }

    /**
     * Returns the contact ID to be used for leave request api
     *
     * @return {Object}
     */
    function prepareContactID() {
      if (vm.filters.leaveRequestFilters.selectedUsers) {
        return vm.filters.leaveRequestFilters.selectedUsers.contact_id;
      }

      return {
        "IN": vm.filteredUsers.length ? vm.filteredUsers.map(function (data) {
            return data.contact_id;
          }) : ["user_contact_id"]
      };
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
      var filters = vm.filters.leaveRequestFilters,
        statusFilter = [],
        //get the value for the waiting_approval status
        waitingApprovalID = vm.leaveRequestStatuses.find(function (data) {
          return data.name === 'waiting_approval';
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

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    function contactFilters() {
      var filters = vm.filters.contactFilters;

      return {
        region: filters.region ? filters.region.value : null,
        department: filters.department ? filters.department.value : null,
        level_type: filters.level_type.length ? {
            "IN": filters.level_type.map(function (data) {
              return data.value;
            })
          } : null,
        location: filters.location ? filters.location.value : null
      };
    }

    /**
     * Loads the status option values
     *
     * @return {Promise}
     */
    function loadStatuses() {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          statuses = statuses.concat(allStatus);
          vm.leaveRequestStatuses = statuses;
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

    return vm;
  }
});
