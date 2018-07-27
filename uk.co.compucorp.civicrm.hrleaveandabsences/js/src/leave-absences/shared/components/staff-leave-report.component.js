/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/pub-sub'
], function (_, moment, components) {
  components.component('staffLeaveReport', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (settings) {
      return settings.sharedPathTpl + 'components/staff-leave-report.html';
    }],
    controllerAs: 'report',
    controller: [
      '$log', '$q', '$rootScope', 'checkPermissions', 'AbsencePeriod', 'AbsenceType',
      'Entitlement', 'LeaveRequest', 'OptionGroup', 'pubSub', 'HR_settings',
      'shared-settings', controller
    ]
  });

  function controller ($log, $q, $rootScope, checkPermissions, AbsencePeriod,
    AbsenceType, Entitlement, LeaveRequest, OptionGroup, pubSub,
    HRSettings, sharedSettings) {
    $log.debug('Component: staff-leave-report');

    var statusUpdateHandlers = {
      delete: removeLeaveRequestFromItsSection,
      cancel: cancelLeaveRequestStatusHandler
    };
    var vm = this;

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.absenceTypesFiltered = [];
    vm.absenceTypesIndexed = {};
    vm.dateFormat = HRSettings.DATE_FORMAT;
    vm.leaveRequestStatuses = {};
    vm.selectedPeriod = null;
    vm.role = null;
    vm.loading = {
      content: true,
      page: true
    };
    vm.sections = {
      approved: { open: false, data: [], dataIndex: {}, loading: false, loadLeaveRequests: loadApprovedRequests },
      entitlements: { open: false, data: [], dataIndex: {}, loading: false, loadLeaveRequests: loadEntitlementsBreakdown },
      expired: { open: false, data: [], dataIndex: {}, loading: false, loadLeaveRequests: loadExpiredBalanceChanges },
      holidays: { open: false, data: [], dataIndex: {}, loading: false, loadLeaveRequests: loadPublicHolidaysRequests },
      pending: { open: false, data: [], dataIndex: {}, loading: false, loadLeaveRequests: loadPendingRequests },
      other: { open: false, data: [], dataIndex: {}, loading: false, loadLeaveRequests: loadOtherRequests }
    };

    vm.labelPeriod = labelPeriod;
    vm.refresh = refresh;
    vm.toggleSection = toggleSection;

    (function init () {
      $q.all([
        initRole(),
        loadStatuses(),
        loadAbsenceTypes(),
        loadAbsencePeriods()
      ])
        .then(function () {
          vm.loading.page = false;
        })
        .then(function () {
          return $q.all([
            loadEntitlements(),
            loadBalanceChanges()
          ]);
        })
        .then(processAbsenceTypes)
        .then(function () {
          vm.loading.content = false;
        });

      registerEvents();
    })();

    /**
     * Adds a leave request to the provided section's data and index.
     *
     * @param {LeaveRequestInstance} leaveRequest - The Leave Request to add
     *   to the section.
     * @param {Object} section - The section object that will hold the leave
     *   request.
     */
    function addLeaveRequestToSection (leaveRequest, section) {
      section.data.push(leaveRequest);
      section.dataIndex[leaveRequest.id] = leaveRequest;
    }

    /**
     * Attaches the entitlement information to the absence type it belongs to
     * If there is no entitlement for a given absence type, a default
     * entitlement object is assigned instead
     */
    function attachEntitlementsToAbsenceTypes () {
      vm.absenceTypes = vm.absenceTypes.map(function (absenceType) {
        var entitlement = _.find(vm.entitlements, function (entitlement) {
          return entitlement.type_id === absenceType.id;
        });

        // set entitlement to 0 if no entitlement is present
        absenceType.entitlement = entitlement ? entitlement.value : 0;
        absenceType.remainder = entitlement ? entitlement.remainder : { current: 0, future: 0 };

        return absenceType;
      });
    }

    /**
     * Handles the cancel status update of leave request by removing them from
     * their current section and adding them to the "Cancelled and Other" section
     * in case the other section is open.
     *
     * @param {LeaveRequestInstance} leaveRequest - the leave request that
     *   triggered the cancel status update.
     */
    function cancelLeaveRequestStatusHandler (leaveRequest) {
      removeLeaveRequestFromItsSection(leaveRequest);
      vm.sections.other.open && addLeaveRequestToSection(leaveRequest,
        vm.sections.other);
    }

    /**
     * Clears the cached data of all sections
     */
    function clearSectionsData () {
      Object.values(vm.sections)
        .forEach(function (section) {
          section.data = [];
        });
    }

    /**
     * Filters the absence types, keeping only the ones that either have
     * an entitlement greater than 0 or that allow overuse or accrual requests
     */
    function filterAbsenceTypes () {
      vm.absenceTypesFiltered = vm.absenceTypes.filter(function (absenceType) {
        return !((absenceType.entitlement === 0) &&
          (absenceType.allow_overuse !== '1') &&
          (absenceType.allow_accruals_request !== '1'));
      });
    }

    /**
     * Forwards the status update event to a specific status handler. If none
     * exists for the given status, a refresh is triggered.
     *
     * @param {Object} statusUpdate - the status update event data. Contains
     *   the status and the leave request that triggered the event.
     */
    function handleStatusUpdate (statusUpdate) {
      var handler = statusUpdateHandlers[statusUpdate.status];

      if (handler) {
        handler(statusUpdate.leaveRequest);
      } else {
        vm.refresh();
      }
    }

    /**
     * Indexes the leave request data of a section and stores it in the
     * dataIndex attribute.
     *
     * @param {Object} section - the section object that contains data to index.
     */
    function indexSectionData (section) {
      section.dataIndex = _.indexBy(section.data, 'id');
    }

    /**
     * Initiates the role based on permissions.
     * @NOTE It skips the permission check if the section is 'absence-tab'
     * because only admins can access this section.
     */
    function initRole () {
      vm.role = 'staff';

      if ($rootScope.section === 'absence-tab') {
        vm.role = 'admin';

        return;
      }

      return checkPermissions(sharedSettings.permissions.admin.administer)
        .then(function (isAdmin) {
          isAdmin && (vm.role = 'admin');
        });
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
          vm.selectedPeriod = _.find(vm.absencePeriods, function (period) {
            return period.current === true;
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
        .then(AbsenceType.loadCalculationUnits)
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
          vm.absenceTypesIndexed = _.indexBy(absenceTypes, 'id');
        });
    }

    /**
     * Loads the approved requests
     *
     * @return {Promise}
     */
    function loadApprovedRequests () {
      return loadLeaveRequestsForSection('approved', {
        status_id: valueOfRequestStatus(sharedSettings.statusNames.approved)
      });
    }

    /**
     * Loads the balance changes of the various sections
     * and groups them by absence type
     *
     * @return {Promise}
     */
    function loadBalanceChanges () {
      var basicParams = { contact_id: vm.contactId, period_id: vm.selectedPeriod.id };

      return $q.all([
        LeaveRequest.balanceChangeByAbsenceType(_.assign({}, basicParams, {
          public_holiday: true
        })),
        LeaveRequest.balanceChangeByAbsenceType(_.assign({}, basicParams, {
          expired: true
        })),
        LeaveRequest.balanceChangeByAbsenceType(_.assign({}, basicParams, {
          statuses: {
            in: [ valueOfRequestStatus(sharedSettings.statusNames.approved) ]
          }
        })),
        LeaveRequest.balanceChangeByAbsenceType(_.assign({}, basicParams, {
          statuses: {
            in: [
              valueOfRequestStatus(sharedSettings.statusNames.awaitingApproval),
              valueOfRequestStatus(sharedSettings.statusNames.moreInformationRequired)
            ]
          }
        }))
      ])
        .then(function (results) {
          vm.absenceTypes.forEach(function (absenceType) {
            absenceType.balanceChanges = {
              holidays: results[0][absenceType.id],
              expired: results[1][absenceType.id],
              approved: results[2][absenceType.id],
              pending: results[3][absenceType.id]
            };
          });
        });
    }

    /**
     * Loads the entitlements, including current and future balance
     *
     * @return {Promise}
     */
    function loadEntitlements () {
      return Entitlement.all({
        contact_id: vm.contactId,
        period_id: vm.selectedPeriod.id
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
    function loadEntitlementsBreakdown () {
      return Entitlement.breakdown({
        contact_id: vm.contactId,
        period_id: vm.selectedPeriod.id
      }, vm.entitlements)
        .then(function () {
          return processBreakdownsList(vm.entitlements);
        })
        .then(function (breakdownListFlatten) {
          vm.sections.entitlements.data = breakdownListFlatten;
        });
    }

    /**
     * Loads the expired balance changes (Brought Forward, TOIL)
     *
     * @return {Promise}
     */
    function loadExpiredBalanceChanges () {
      return $q.all([
        Entitlement.breakdown({
          contact_id: vm.contactId,
          period_id: vm.selectedPeriod.id,
          expired: true
        }),
        loadLeaveRequests({
          request_type: 'toil',
          expired: true
        })
      ])
        .then(function (results) {
          return $q.all({
            expiredBalanceChangesFlatten: processBreakdownsList(results[0]),
            expiredTOILS: processExpiredTOILS(results[1].list)
          });
        })
        .then(function (results) {
          vm.sections.expired.data = results.expiredBalanceChangesFlatten.concat(results.expiredTOILS);
          vm.sections.expired.data = _.sortBy(vm.sections.expired.data, 'expiry_date');
        });
    }

    /**
     * Fetches leave requests for the contact, for the available absence types,
     * for the selected period and optionally by extra parameters
     *
     * @param  {Object} params extra parameters for the leave requests search
     * @return {Promise} resolves with a response from API containing leave requests
     */
    function loadLeaveRequests (params) {
      return LeaveRequest.all(_.assign({
        contact_id: vm.contactId,
        from_date: { from: vm.selectedPeriod.start_date },
        to_date: { to: vm.selectedPeriod.end_date },
        type_id: {
          IN: vm.absenceTypes.map(function (absenceType) {
            return absenceType.id;
          })
        }
      }, params), null, 'from_date ASC', null, false);
    }

    /**
     * Loads leave requests and populates them to the given section
     *
     * @param  {String} section
     * @param  {Object} params extra parameters for the leave requests search
     * @return {Promise}
     */
    function loadLeaveRequestsForSection (section, params) {
      return loadLeaveRequests(params)
        .then(function (leaveRequests) {
          vm.sections[section].data = leaveRequests.list;
        });
    }

    /**
     * Loads the data of all the currently opened sections
     *
     * @return {Promise}
     */
    function loadOpenSectionsData () {
      return $q.all(Object.values(vm.sections)
        .filter(function (section) {
          return section.open;
        })
        .map(loadSectionLeaveRequests));
    }

    /**
     * Loads the rejected/cancelled leave requests
     *
     * @return {Promise}
     */
    function loadOtherRequests () {
      return loadLeaveRequestsForSection('other', {
        status_id: { in: [
          valueOfRequestStatus(sharedSettings.statusNames.rejected),
          valueOfRequestStatus(sharedSettings.statusNames.cancelled)
        ] }
      });
    }

    /**
     * Loads the currently pending leave requests
     *
     * @return {Promise}
     */
    function loadPendingRequests () {
      return loadLeaveRequestsForSection('pending', {
        status_id: { in: [
          valueOfRequestStatus(sharedSettings.statusNames.awaitingApproval),
          valueOfRequestStatus(sharedSettings.statusNames.moreInformationRequired)
        ] }
      });
    }

    /**
     * Loads leave requests associated to public holidays
     *
     * @return {Promise}
     */
    function loadPublicHolidaysRequests () {
      return loadLeaveRequestsForSection('holidays', {
        public_holiday: true
      });
    }

    /**
     * Loads leave requests for each section, indexes the loaded data,
     * and puts the section in and out of loading mode
     *
     * @param  {Object} section
     * @return {Promise}
     */
    function loadSectionLeaveRequests (section) {
      section.loading = true;

      return section.loadLeaveRequests()
        .then(indexSectionData.bind(this, section))
        .then(function () {
          section.loading = false;
        });
    }

    /**
     * NOTE: This should be just temporary, see PCHR-1810
     * Loads all the possible statuses of a leave request
     *
     * @return {Promise}
     */
    function loadStatuses () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          vm.leaveRequestStatuses = _.indexBy(statuses, 'value');
        });
    }

    /**
     * Process the list of absence types objects by augmenting and filter them
     */
    function processAbsenceTypes () {
      attachEntitlementsToAbsenceTypes();
      filterAbsenceTypes();
    }

    /**
     * For each breakdowns, it sets the absence type id to
     * each list entry (based on the entitlement they belong to)
     * and flattens the result in the end to get one single list
     *
     * @param  {Array} list
     *   each breakdown should contain `id` and `breakdown` properties
     * @return {Promise} resolves to the flatten list
     */
    function processBreakdownsList (list) {
      return $q.resolve()
        .then(function () {
          return list.map(function (listEntry) {
            var entitlement = _.find(vm.entitlements, function (entitlement) {
              return entitlement.id === listEntry.id;
            });

            return listEntry.breakdown.map(function (breakdownEntry) {
              return _.assign(_.clone(breakdownEntry), {
                type_id: entitlement.type_id
              });
            });
          });
        })
        .then(function (breakdownList) {
          return Array.prototype.concat.apply([], breakdownList);
        });
    }

    /**
     * Process each expired TOIL request, so that they have the same
     * key properties that an entitlement breakdown object has, given that
     * they need to be listed in the same section
     *
     * @param  {Array} toils
     * @return {Promise} resolves to {Array}
     */
    function processExpiredTOILS (toils) {
      return $q.resolve()
        .then(function () {
          return toils.map(function (toil) {
            return _.assign({}, toil, {
              expiry_date: toil.toil_expiry_date,
              amount: toil.toil_to_accrue,
              type: {
                'label': 'Accrued TOIL'
              }
            });
          });
        });
    }

    /**
     * Register events which will be called by other modules
     */
    function registerEvents () {
      pubSub.subscribe('LeaveRequest::new', function () { vm.refresh(); });
      pubSub.subscribe('LeaveRequest::edit', function () { vm.refresh(); });
      pubSub.subscribe('LeaveRequest::statusUpdate', handleStatusUpdate);
      pubSub.subscribe('LeaveRequest::delete', function (leaveRequest) {
        removeLeaveRequestFromItsSection(leaveRequest);
      });
    }

    /**
     * Removes the given leave request from the section it currently belongs to.
     * After the request is removed, it recalculates the balance change
     * for the section.
     *
     * @param  {LeaveRequestInstance} leaveRequest
     */
    function removeLeaveRequestFromItsSection (leaveRequest) {
      _.forEach(vm.sections, function (section, sectionName) {
        var requestIndexInSection = section.dataIndex[leaveRequest.id];

        if (!requestIndexInSection) {
          return;
        }

        _.remove(section.data, function (dataEntry) {
          return dataEntry.id === leaveRequest.id;
        });
        delete section.dataIndex[leaveRequest.id];

        if (sectionName !== 'other') {
          updateSectionNumbersWithLeaveRequestBalanceChange(leaveRequest, sectionName);
        }
      });
    }

    /**
     * Recalculates the section's balance change and remainder numbers with the
     * given leave request's balance change
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {string} section
     */
    function updateSectionNumbersWithLeaveRequestBalanceChange (leaveRequest, section) {
      var remaindersToUpdate = ['future', 'current'];
      var absenceType = vm.absenceTypesIndexed[leaveRequest.type_id];

      (section === 'pending') && _.pull(remaindersToUpdate, 'current');

      absenceType.balanceChanges[section] -= leaveRequest.balance_change;
      remaindersToUpdate.forEach(function (remainder) {
        absenceType.remainder[remainder] -= leaveRequest.balance_change;
      });
    }

    /**
     * Refreshes all data that is dependend on the selected absence period,
     * and clears the cached data of closed sections
     */
    function refresh () {
      vm.loading.content = true;

      $q.all([
        loadEntitlements(),
        loadBalanceChanges()
      ])
        .then(processAbsenceTypes)
        .then(function () {
          vm.loading.content = false;
        })
        .then(function () {
          return $q.all([
            loadOpenSectionsData(),
            clearSectionsData()
          ]);
        });
    }

    /**
     * Opens/closes the given section. When opening it triggers the
     * load function if no cached data is present
     *
     * @param {string} sectionName
     */
    function toggleSection (sectionName) {
      var section = vm.sections[sectionName];
      section.open = !section.open;

      if (section.open && !section.data.length) {
        loadSectionLeaveRequests(section);
      }
    }

    /**
     * Returns the value of the given leave request status
     *
     * @param  {string} statusName
     * @return {integer}
     */
    function valueOfRequestStatus (statusName) {
      return _.find(vm.leaveRequestStatuses, function (status) {
        return status.name === statusName;
      })['value'];
    }
  }
});
