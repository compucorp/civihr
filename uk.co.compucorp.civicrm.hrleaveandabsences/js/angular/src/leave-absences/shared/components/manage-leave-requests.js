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
    controller: [
      '$controller', '$log', '$q', '$rootScope', 'shared-settings', 'checkPermissions',
      'Contact', 'AbsencePeriod', 'AbsenceType', 'LeaveRequest', 'OptionGroup',
      'dialog', controller]
  });

  function controller ($controller, $log, $q, $rootScope, sharedSettings, checkPermissions, Contact, AbsencePeriod, AbsenceType, LeaveRequest, OptionGroup, dialog) {
    'use strict';
    $log.debug('Component: manage-leave-requests');

    var vm = this;
    var actionMatrix = {};

    actionMatrix[sharedSettings.statusNames.awaitingApproval] = ['respond', 'cancel', 'approve', 'reject'];
    actionMatrix[sharedSettings.statusNames.moreInformationRequired] = ['edit', 'cancel'];
    actionMatrix[sharedSettings.statusNames.approved] = ['edit'];
    actionMatrix[sharedSettings.statusNames.cancelled] = ['edit'];
    actionMatrix[sharedSettings.statusNames.rejected] = ['edit'];

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
        contact_id: null,
        selectedPeriod: null,
        selectedAbsenceTypes: null
      }
    };
    vm.filteredUsers = [];
    vm.isFilterExpanded = false;
    // leaveRequests.table - to handle table data
    // leaveRequests.filter - to handle left nav filter data
    vm.isAdmin = false; // this property is updated on controller initialization
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
     * Returns the available actions, based on the current status
     * of the given leave request
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {Array}
     */
    vm.actionsFor = function (leaveRequest) {
      var statusKey = _.find(vm.leaveRequestStatuses, function (status) {
        return status.value ? status.value === leaveRequest.status_id : false;
      }).name;

      return statusKey ? actionMatrix[statusKey] : [];
    };

    /**
     * Performs an action on a given leave request
     * TODO: refactor when adding more actions
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {string} action
     */
    vm.action = function (leaveRequest, action) {
      var map = {
        cancel: {
          title: 'Cancellation',
          btnClass: 'danger',
          btnLabel: 'Confirm',
          msg: 'This cannot be undone'
        },
        approve: {
          title: 'Approval',
          btnClass: 'success',
          btnLabel: 'Approve',
          msg: 'Please confirm approval'
        },
        reject: {
          title: 'Rejection',
          btnClass: 'warning',
          btnLabel: 'Reject',
          msg: 'Please confirm rejection'
        }
      };

      dialog.open({
        title: 'Confirm ' + map[action].title + '?',
        copyCancel: 'Cancel',
        copyConfirm: map[action].btnLabel,
        classConfirm: 'btn-' + map[action].btnClass,
        msg: map[action].msg,
        onConfirm: function () {
          return leaveRequest[action]();
        }
      });
    };

    /**
     * Clears selected users and refreshes leave requests
     */
    vm.clearStaffSelection = function () {
      vm.filters.leaveRequest.contact_id = null;
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
        return request.status_id === status.value;
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
        return status.value === value;
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
    };

    /**
     * Returns the username when id is given
     *
     * @param {string} id - id of the user
     * @return {string}
     */
    vm.getUserNameByID = function (id) {
      var user = _.find(vm.filteredUsers, function (contact) {
        return contact.id === id;
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
        managed_by: vm.isAdmin ? undefined : vm.contactId, // managed_by must be ignored if admin
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
      // get the value for the waiting_approval status
      var waitingApprovalID = _.find(vm.leaveRequestStatuses, function (status) {
        return status.name === sharedSettings.statusNames.awaitingApproval;
      }).value;

      // if filterByStatus is true then add the leaveStatus to be used in the leave request api
      if (filterByStatus && filters.leaveStatus && filters.leaveStatus.value) {
        statusFilter.push(filters.leaveStatus.value);
      }

      // if pending_requests is true then add the awaiting_approval to be used in the leave request api
      if (filters.pending_requests && waitingApprovalID) {
        statusFilter.push(waitingApprovalID);
      }

      if (statusFilter.length) {
        return {
          'IN': statusFilter
        };
      }
    }

    /**
     * Register events which will be called by other modules
     */
    function registerEvents () {
      $rootScope.$on('LeaveRequest::updatedByManager', vm.refresh);
      $rootScope.$on('LeaveRequest::new', vm.refresh);
    }
  }
});
