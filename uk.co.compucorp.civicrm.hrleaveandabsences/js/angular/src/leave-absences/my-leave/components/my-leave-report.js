define([
  'leave-absences/my-leave/modules/components',
  'common/lodash'
], function (components, _) {

  components.component('myLeaveReport', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-report.html';
    }],
    controllerAs: 'report',
    controller: [
      '$log', '$q', 'AbsencePeriod', 'AbsenceType', 'Entitlement', 'LeaveRequest',
      'OptionGroup', 'dialog', controller
    ]
  });


  function controller($log, $q, AbsencePeriod, AbsenceType, Entitlement, LeaveRequest, OptionGroup, dialog) {
    $log.debug('Component: my-leave-report');

    var vm = Object.create(this);
    var actionMatrix = {
      'waiting_approval'          : ['edit'   , 'cancel'],
      'more_information_requested': ['respond', 'cancel'],
      'approved'                  : ['cancel'           ],
      'cancelled'                 : [                   ],
      'rejected'                  : [                   ]
    };

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.balanceChanges = {};
    vm.currentPeriod = null;
    vm.leaveRequestStatuses = [];
    vm.loading = true;
    vm.sections = {
      approved:     { isOpen: false, data: [], loadFn: loadApprovedRequests },
      entitlements: { isOpen: false, data: [], loadFn: loadEntitlementsBreakdown },
      expired:      { isOpen: false, data: [], loadFn: loadExpiredBalanceChanges },
      holidays:     { isOpen: false, data: [], loadFn: loadPublicHolidays },
      open:         { isOpen: false, data: [], loadFn: loadPendingRequests },
      other:        { isOpen: false, data: [], loadFn: loadOtherRequests }
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
        return status.id === leaveRequest.status_id;
      })['name'];

      return statusKey ? actionMatrix[statusKey] : [];
    };

    /**
     * Cancels a leave request, but asks for user's confirmation first
     *
     * @param {LeaveRequestInstance} leaveRequest
     */
    vm.cancelRequest = function (leaveRequest) {
      dialog.open({
        title: 'Confirm Cancellation Leave Request',
        copyCancel: 'Cancel',
        copyConfirm: 'Confirm',
        classConfirm: 'btn-danger',
        msg: 'This cannot be undone'
      })
      .then(function (response) {
        !!response && cancelRequest(leaveRequest);
      });
    };

    /**
     * Changes the current period and reloads all related data
     *
     * @param {AbsencePeriodInstance} newPeriod
     */
    vm.changePeriod = function (newPeriod) {
      vm.currentPeriod = newPeriod;
      vm.loading = true;

      $q.all([
        loadEntitlements(),
        loadBalanceChanges(),
        loadOpenSectionsData()
      ])
      .then(function () {
        clearClosedSectionsData();
      })
      .then(function () {
        vm.loading = false;
      });
    };

    /**
     * Open the given section, triggering the load function if no
     * cached data is present
     *
     * @param {string} sectionName
     */
    vm.openSection = function (sectionName) {
      var section = vm.sections[sectionName];

      if (!section.data.length) {
        section.loadFn();
      }
    };

    // Init block
    (function init() {
      $q.all([
        loadStatuses(),
        loadAbsenceTypes(),
        loadAbsencePeriods()
      ])
      .then(function () {
        return $q.all([
          loadEntitlements(),
          loadBalanceChanges()
        ]);
      })
      .then(function () {
        vm.loading = false;
      });
    })();

    /**
     * Triggers the cancellation action, then removes the cancelled
     * leave request from either the "approved", or "open" sections (the only
     * sections where a leave request can be cancelled), and moves it to the
     * "other" section
     *
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function cancelRequest(leaveRequest) {
      leaveRequest.cancel().then(function () {
        [vm.sections.approved, vm.sections.open].forEach(function (section) {
          _.remove(section.data, function (dataEntry) {
            return dataEntry.id === leaveRequest.id;
          });
        });
      })
      .then(function () {
        vm.sections.other.data.push(leaveRequest);
      });
    }

    /**
     * Clears the cached data of all the closed sections
     */
    function clearClosedSectionsData() {
      Object.values(vm.sections)
        .filter(function (section) {
          return !section.isOpen;
        })
        .forEach(function (section) {
          section.data = [];
        });
    }

    /**
     * Returns the id of the given leave request status
     *
     * @param  {string} statusName
     * @return {integer}
     */
    function idOfRequestStatus(statusName) {
      return _.find(vm.leaveRequestStatuses, function (status) {
        return status.name === statusName;
      })['id'];
    }

    /**
     * NOTE: This should be just temporary, see PCHR-1810
     * Loads all the possible statuses of a leave request
     *
     * @return {Promise}
     */
    function loadStatuses() {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          vm.leaveRequestStatuses = statuses;
        });
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
          vm.currentPeriod = _.find(vm.absencePeriods, function (period) {
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
     * Loads the approved requests
     *
     * @return {Promise}
     */
    function loadApprovedRequests() {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.currentPeriod.start_date },
        to_date: { to: vm.currentPeriod.end_date },
        status: idOfRequestStatus('approved')
      })
      .then(function (leaveRequests) {
        vm.sections.approved.data = leaveRequests;
      });
    }

    /**
     * Loads the balance changes of the various sections
     *
     * @return {Promise}
     */
    function loadBalanceChanges() {
      return $q.all([
        LeaveRequest.balanceChangeByAbsenceType(vm.contactId, vm.currentPeriod.id, null, true),
        LeaveRequest.balanceChangeByAbsenceType(vm.contactId, vm.currentPeriod.id, [
          idOfRequestStatus('approved')
        ]),
        LeaveRequest.balanceChangeByAbsenceType(vm.contactId, vm.currentPeriod.id, [
          idOfRequestStatus('waiting_approval'),
          idOfRequestStatus('more_information_requested')
        ])
      ])
      .then(function (results) {
        vm.balanceChanges.public_holidays = results[0];
        vm.balanceChanges.approved = results[1];
        vm.balanceChanges.open = results[2];
      });
    }

    /**
     * Loads the entitlements, including current and future balance
     *
     * @return {Promise}
     */
    function loadEntitlements() {
      return Entitlement.all({
        contact_id: vm.contactId,
        period_id: vm.currentPeriod.id
      }, true)
      .then(function (entitlements) {
        vm.entitlements = entitlements;
      });
    }

    /**
     * Loads the entitlements breakdown
     *
     * @return {Promise}
     */
    function loadEntitlementsBreakdown() {
      return Entitlement.breakdown({
        contact_id: vm.contactId,
        period_id: vm.currentPeriod.id
      }, vm.entitlements)
      .then(function () {
        // Flattens the breakdowns array
        return Array.prototype.concat.apply([], vm.entitlements.map(function (entitlement) {
          return entitlement.breakdown;
        }));
      })
      .then(function (breakdown) {
        vm.sections.entitlements.data = breakdown;
      });
    }

    /**
     * Loads the expired balance changes (Brought Forward, TOIL)
     *
     * @return {Promise}
     */
    function loadExpiredBalanceChanges() {
      return Entitlement.breakdown({
        contact_id: vm.contactId,
        period_id: vm.currentPeriod.id,
        expired: true
      })
      .then(function (expiredBalanceChanges) {
        vm.sections.expired.data = expiredBalanceChanges;
      });
    }

    /**
     * Loads the data of all the currently opened sections
     *
     * @return {Promise}
     */
    function loadOpenSectionsData() {
      return $q.all(Object.values(vm.sections)
        .filter(function (section) {
          return section.isOpen;
        })
        .map(function (section) {
          return section.loadFn();
        }));
    }

    /**
     * Loads the rejected/cancelled leave requests
     *
     * @return {Promise}
     */
    function loadOtherRequests() {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.currentPeriod.start_date },
        to_date: { to: vm.currentPeriod.end_date },
        status: { in: [
          idOfRequestStatus('rejected'),
          idOfRequestStatus('cancelled')
        ] }
      })
      .then(function (leaveRequests) {
        vm.sections.other.data = leaveRequests;
      });
    }

    /**
     * Loads the currently pending leave requests
     *
     * @return {Promise}
     */
    function loadPendingRequests() {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.currentPeriod.start_date },
        to_date: { to: vm.currentPeriod.end_date },
        status: { in: [
          idOfRequestStatus('waiting_approval'),
          idOfRequestStatus('more_information_requested')
        ] }
      })
      .then(function (leaveRequests) {
        vm.sections.open.data = leaveRequests;
      });
    }

    /**
     * Loads the leave requests associated to public holidays
     *
     * @return {Promise}
     */
    function loadPublicHolidays() {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.currentPeriod.start_date },
        to_date: { to: vm.currentPeriod.end_date },
        public_holiday: true
      })
      .then(function (leaveRequests) {
        vm.sections.holidays.data = leaveRequests;
      });
    }

    return vm;
  }
});
