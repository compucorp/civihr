define([
  'leave-absences/manager-leave/modules/components',
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
    controller: ['$log', '$q', 'Contact', 'AbsencePeriod', 'AbsenceType', 'LeaveRequest',
      'OptionGroup', controller]
  });


  function controller($log, $q, Contact, AbsencePeriod, AbsenceType, LeaveRequest, OptionGroup) {
    $log.debug('Component: manager-leave-requests');

    var vm = Object.create(this);

    vm.isFilterExpanded = true;
    vm.absencePeriods = [];

    vm.absenceTypes = [];

    vm.selectedLeaveStatus = "";

    vm.leaveRequests = {
      list: []
    };

    vm.filters = {
      contactFilters: {
        region: '',
        department: '',
        level_type: '',
        location: ''
      },
      leaveRequestFilters: {
        selectedPeriod: "",
        selectedAbsenceTypes: "",
        pending_requests: false
      }
    };

    vm.pagination = {
      page: 1,
      size: 7
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
          loadAllRequests(1);
        })
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
      Contact.all(contactFilters())
        .then(function (data) {
          // console.log('data', data);
          vm.filteredUsers = data.list;

          return LeaveRequest.all(leaveRequestFilters(), vm.pagination)
            .then(function (leaveRequests) {
              // console.log(leaveRequests);
              vm.leaveRequests = leaveRequests;
            });
        });
      // console.log("contactId", vm.contactId);

    }

    function leaveRequestFilters() {

      var filters = vm.filters.leaveRequestFilters;
      return {
        managed_by: vm.contactId,
        type_id: filters.selectedAbsenceTypes ? filters.selectedAbsenceTypes.id : null,
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
          // console.log('Status', vm.leaveRequestStatuses)
        });
    }

    function loadRegions() {
      return OptionGroup.valuesOf('hrjc_region')
        .then(function (regions) {
          // console.log('regions', regions);
          vm.regions = regions;
        });
    }

    function loadDepartments() {
      return OptionGroup.valuesOf('hrjc_department')
        .then(function (departments) {
          // console.log('departments', departments);
          vm.departments = departments;
        });
    }

    function loadLocations() {
      return OptionGroup.valuesOf('hrjc_location')
        .then(function (locations) {
          // console.log('locations', locations);
          vm.locations = locations;
        });
    }

    function loadLevelTypes() {
      return OptionGroup.valuesOf('hrjc_level_type')
        .then(function (levels) {
          // console.log('levels', levels);
          vm.levelTypes = levels;
        });
    }

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {AbsencePeriodInstance} period
     * @return {string}
     */
    vm.labelPeriod = function (period) {
      return period.current ? 'Current Period (' + period.title + ')' : period.title;
    };

    vm.getLeaveStatusByID = function (id) {
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
      if (status.name === 'all') {
        return vm.leaveRequests.list;
      }

      return vm.leaveRequests.list.filter(function (request) {
        return request.status_id == status.value;
      })
    };

    vm.getNavBadge = function (name) {
      if (name === 'approved') {
        return 'badge-success'
      } else if (name === 'rejected') {
        return 'badge-danger'
      } else if (name === 'cancelled' || name === 'all') {
        return ''
      } else {
        return 'badge-primary'
      }
    };

    vm.range = function (n) {
      if (n) {
        return new Array(Math.ceil(n));
      }
    };

    vm.refresh = function () {
      loadAllRequests(1);
    };

    return vm;
  }
});
