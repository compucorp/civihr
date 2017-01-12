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

    var vm = Object.create(this);

    vm.isFilterExpanded = false;
    vm.absencePeriods = [];
    vm.filteredUsers = [];
    vm.absenceTypes = [];
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
        level_type: null,
        location: null
      },
      leaveRequestFilters: {
        selectedPeriod: null,
        selectedAbsenceTypes: null,
        leaveStatus: null,
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

    vm.getLeaveStatusByValue = function (id) {
      if (vm.leaveRequestStatuses.length) {
        return vm.leaveRequestStatuses.find(function (status) {
          return status.value == id;
        }).label;
      }
    };

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

    vm.filterLeaveRequestByStatus = function (status) {
      if (status.name === 'all' || status === '') {
        return vm.leaveRequests.filter.list;
      }

      return vm.leaveRequests.filter.list.filter(function (request) {
        return request.status_id == status.value;
      })
    };

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

    vm.range = function (n) {
      if (n) {
        return new Array(n);
      }
    };

    vm.totalNoOfPages = function () {
      return Math.ceil(vm.leaveRequests.table.total / vm.pagination.size);
    };

    vm.nextPage = function () {
      if (vm.pagination.page < vm.totalNoOfPages()) {
        vm.refresh(++vm.pagination.page);
      }
    };

    vm.refresh = function (page) {
      page = page ? page : 1;
      loadAllRequests(page);
    };

    vm.refreshWithFilter = function (status) {
      vm.filters.leaveRequestFilters.leaveStatus = status;
      vm.refresh();
    };

    vm.getUserNameByID = function (id) {
      return vm.filteredUsers.find(function (data) {
        return data.contact_id == id;
      }).display_name;
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
          vm.refresh();
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
    function loadAllRequests(page) {
      vm.pagination.page = page;
      vm.loading.content = true;
      Contact.all(contactFilters())
        .then(function (data) {
          vm.filteredUsers = data.list;

          $q.all([
            loadLeaveRequest('table'),
            loadLeaveRequest('filter')
          ])
            .then(function () {
              vm.loading.content = false;
            })

        });
    }

    function loadLeaveRequest(type) {
      debugger;
      var filterByStatus = type !== 'filter',
        pagination = type === 'filter' ? {} : vm.pagination;
      return LeaveRequest.all(leaveRequestFilters(filterByStatus), pagination)
        .then(function (leaveRequests) {
          debugger;
          vm.leaveRequests[type] = leaveRequests;
        });
    }

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
        contact_id: {
          "IN": vm.filteredUsers.length ? vm.filteredUsers.map(function (data) {
              return data.contact_id;
            }) : ["user_contact_id"]
        }
      };
    }

    function prepareStatusFilter(filterByStatus) {
      var filters = vm.filters.leaveRequestFilters,
        statusFilter = [],
        waitingApprovalID = vm.leaveRequestStatuses.find(function (data) {
          return data.name === 'waiting_approval';
        }).value;

      if(filterByStatus && filters.leaveStatus && filters.leaveStatus.value) {
        statusFilter.push(filters.leaveStatus.value);
      }

      if(filters.pending_requests && waitingApprovalID) {
        statusFilter.push(waitingApprovalID);
      }

      if(statusFilter.length) {
        return {
          "IN": statusFilter
        }
      }
    }

    function contactFilters() {
      var filters = vm.filters.contactFilters;

      return {
        region: filters.region ? filters.region.id : null,
        department: filters.department ? filters.department.id : null,
        level_type: filters.level_type ? filters.level_type.id : null,
        location: filters.location ? filters.location.id : null
      };
    }

    function loadStatuses() {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          statuses = statuses.concat({
            name: 'all',
            label: 'All'
          });
          vm.leaveRequestStatuses = statuses;
        });
    }

    function loadRegions() {
      return OptionGroup.valuesOf('hrjc_region')
        .then(function (regions) {
          vm.regions = regions;
        });
    }

    function loadDepartments() {
      return OptionGroup.valuesOf('hrjc_department')
        .then(function (departments) {
          vm.departments = departments;
        });
    }

    function loadLocations() {
      return OptionGroup.valuesOf('hrjc_location')
        .then(function (locations) {
          vm.locations = locations;
        });
    }

    function loadLevelTypes() {
      return OptionGroup.valuesOf('hrjc_level_type')
        .then(function (levels) {
          vm.levelTypes = levels;
        });
    }

    return vm;
  }
});
