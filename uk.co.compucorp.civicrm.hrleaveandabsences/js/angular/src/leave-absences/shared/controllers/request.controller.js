/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'common/models/contact',
  'common/models/session.model',
  'common/services/api/option-group',
  'common/services/hr-settings',
  'common/services/pub-sub',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/models/entitlement.model',
  'leave-absences/shared/models/leave-request.model',
  'leave-absences/shared/models/public-holiday.model',
  'leave-absences/shared/instances/leave-request.instance',
  'leave-absences/shared/instances/sickness-request.instance',
  'leave-absences/shared/instances/toil-request.instance',
  'leave-absences/shared/services/leave-request.service'
], function (angular, controllers, _, moment) {
  'use strict';

  controllers.controller('RequestCtrl', RequestCtrl);

  RequestCtrl.$inject = ['$log', '$q', '$rootScope', '$scope', '$uibModalInstance', 'checkPermissions', 'api.optionGroup',
    'dialog', 'pubSub', 'directiveOptions', 'Contact', 'Session', 'AbsencePeriod', 'AbsenceType', 'Entitlement',
    'LeaveRequest', 'LeaveRequestInstance', 'shared-settings', 'SicknessRequestInstance', 'TOILRequestInstance', 'LeaveRequestService'];

  function RequestCtrl ($log, $q, $rootScope, $scope, $modalInstance, checkPermissions, OptionGroup, dialog, pubSub,
    directiveOptions, Contact, Session, AbsencePeriod, AbsenceType, Entitlement, LeaveRequest,
    LeaveRequestInstance, sharedSettings, SicknessRequestInstance, TOILRequestInstance, LeaveRequestService) {
    $log.debug('RequestCtrl');

    var absenceTypesAndIds;
    var availableStatusesMatrix = {};
    var childComponentsCount = 0;
    var initialLeaveRequestAttributes = {}; // used to compare the change in leaverequest in edit mode
    var listeners = [];
    var loggedInContactId = '';
    var NO_ENTITLEMENT_ERROR = 'No entitlement';
    var role = '';
    var tabs = [];
    var vm = _.assign(this, directiveOptions); // put all directive options directly in the vm

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.canManage = false; // vm flag is set on initialisation of the controller
    vm.contactName = null; // contact name of the owner of leave request
    vm.errors = [];
    vm.loading = { absenceTypes: true, entitlements: true };
    vm.managedContacts = [];
    vm.mode = ''; // can be edit, create, view
    vm.newStatusOnSave = null;
    vm.period = {};
    vm.postContactSelection = false; // flag to track if user is selected for enabling UI
    vm.requestStatuses = {};
    vm.selectedAbsenceType = {};
    vm.staffMemberSelectionComplete = false;
    vm.submitting = false;
    vm.balance = {
      closing: 0,
      opening: 0,
      change: {
        amount: 0,
        breakdown: []
      }
    };

    vm.canChangeAbsenceType = canChangeAbsenceType;
    vm.canSubmit = canSubmit;
    vm.closeAlert = closeAlert;
    vm.deleteLeaveRequest = deleteLeaveRequest;
    vm.dismissModal = dismissModal;
    vm.getStatuses = getStatuses;
    vm.getStatusFromValue = getStatusFromValue;
    vm.initAfterContactSelection = initAfterContactSelection;
    vm.initRequestAttributes = initRequestAttributes;
    vm.isLeaveStatus = isLeaveStatus;
    vm.isLeaveType = isLeaveType;
    vm.isMode = isMode;
    vm.isRole = isRole;
    vm.submit = submit;
    vm.updateAbsenceType = updateAbsenceType;

    /**
     * Initializes the controller on loading the dialog
     *
     * @return {Promise}
     */
    (function init () {
      vm.loading.absenceTypes = true;

      initAvailableStatusesMatrix();
      initListeners();

      return loadLoggedInContactId()
        .then(initIsSelfRecord)
        .then(function () {
          return $q.all([
            initRoles(),
            loadAbsencePeriods(),
            loadStatuses()
          ]);
        })
        .then(initRequest)
        .then(setModalMode)
        .then(setInitialAbsencePeriod)
        .then(function () {
          return vm.canManage && !vm.isMode('edit') && loadManagees();
        })
        .then(function () {
          if (vm.selectedContactId) {
            vm.request.contact_id = vm.selectedContactId;
          }
          // The additional check here prevents error being displayed on startup when no contact is selected
          if (vm.request.contact_id) {
            return vm.initAfterContactSelection();
          }
        })
        .catch(handleError)
        .finally(function () {
          vm.loading.absenceTypes = false;
        });
    }());

    /**
     * Removes [type]_date_amount param from the leave request in case of days unit.
     * In other cases, such as hours unit, removes [type]_date_type param.
     * This is a requirement of the back-end not to pass such params depending on the case.
     *
     * @param {String} type from|to
     */
    function amendDatesAndDateTypesBeforeSave (type) {
      if (vm.selectedAbsenceType.calculation_unit_name === 'days') {
        delete vm.request[type + '_date_amount'];
      } else {
        delete vm.request[type + '_date_type'];
      }
    }

    /**
     * Amends request parameters before submit as per back-end requirements.
     */
    function amendRequestParamsBeforeSave () {
      ['from', 'to'].forEach(amendDatesAndDateTypesBeforeSave);
    }

    /**
     * Broadcasts an event when request has been updated from awaiting approval status to something else
     */
    function broadcastRequestUpdatedEvent () {
      var awaitingApprovalStatusValue = vm.requestStatuses[sharedSettings.statusNames.awaitingApproval].value;

      // Check if the leave request had awaiting approval status before update,
      // and after update the status is not awaiting approval
      if (initialLeaveRequestAttributes.status_id === awaitingApprovalStatusValue &&
        awaitingApprovalStatusValue !== vm.request.status_id) {
        pubSub.publish('ManagerBadge:: Update Count');
      }
    }

    /**
     * Determines if all of the required tabs can be submitted
     *
     * @return {Boolean}
     */
    function canAllRequiredTabsBeSubmitted () {
      return tabs.filter(function (tab) {
        return tab.isRequired;
      }).every(function (tab) {
        return tab.canSubmit && tab.canSubmit();
      });
    }

    /**
     * Determines if at least one of the non-required tabs can be submitted.
     *
     * @return {Boolean}
     */
    function canAnyNonRequiredTabBeSubmitted () {
      return tabs.filter(function (tab) {
        return !tab.isRequired;
      }).some(function (tab) {
        return tab.canSubmit && tab.canSubmit();
      });
    }

    /**
     * Checks if Absence Type can be changed
     * - Disregarding any conditions, if entitlements are being loaded you cannot change
     * - Admins can always change, disregarding the mode
     * - Neither managers nor staff can change in "view" mode
     * - Managers can only change in "create" mode
     *
     * @return {Boolean}
     */
    function canChangeAbsenceType () {
      if (vm.loading.entitlements) {
        return false;
      }

      if (isRole('admin')) {
        return true;
      }

      if (isMode('view')) {
        return false;
      }

      if (isRole('manager') && !isMode('create')) {
        return false;
      }

      return true;
    }

    /**
     * Checks if submit button can be enabled for user and returns true if succeeds
     *
     * @return {Boolean}
     */
    function canSubmit () {
      // checks if one of the tabs can be submitted
      var canSubmit = canAllRequiredTabsBeSubmitted();

      // check if user has changed any attribute
      if (vm.isMode('edit')) {
        canSubmit = canSubmit && (hasRequestChanged() || canAnyNonRequiredTabBeSubmitted());
      }

      // check if manager has changed status
      if (vm.canManage && vm.requestStatuses) {
        // awaiting_approval will not be available in vm.requestStatuses if manager has changed selection
        canSubmit = canSubmit && !!vm.getStatusFromValue(vm.newStatusOnSave);
      }

      // check if the selected date period is in absence period
      canSubmit = canSubmit && !!vm.period.id;

      return canSubmit && !vm.isMode('view');
    }

    /**
     * Changes status of the leave request before saving it
     * When recording for yourself the status_id should be always set to awaitingApproval before saving
     * If manager or admin have changed the status through dropdown, assign the same before calling API
     */
    function changeStatusBeforeSave () {
      if (vm.isSelfRecord) {
        vm.request.status_id = vm.requestStatuses[sharedSettings.statusNames.awaitingApproval].value;
      } else if (vm.canManage) {
        vm.request.status_id = vm.newStatusOnSave || vm.request.status_id;
      }
    }

    /**
     * Checks if the balance change has changed since this leave request was saved
     *
     * @return {Promise}
     */
    function checkIfBalanceChangeHasChanged () {
      if (!vm.isMode('edit') || vm.isRole('staff') || getLeaveType() === 'toil') { return; }

      return vm.request.calculateBalanceChange(vm.selectedAbsenceType.calculation_unit_name)
        .then(function (balanceChange) {
          if (+vm.balance.change.amount !== +balanceChange.amount) {
            LeaveRequestService.promptBalanceChangeRecalculation()
              .then(function () {
                $rootScope.$emit('LeaveRequestPopup::recalculateBalanceChange');
              });

            return $q.reject();
          }
        });
    }

    /**
     * Checks if request dates and times need to be reverted to the original state.
     * They need to be reverted if the balance has not been changed for all requests
     * except TOIL because its balance is independent from the dates and times.
     *
     * @return {Boolean}
     */
    function checkIfRequestDatesAndTimesNeedToBeReverted () {
      return getLeaveType() !== 'toil' && !vm.request.change_balance;
    }

    /**
     * Closes the error alerts if any
     */
    function closeAlert () {
      vm.errors = [];
    }

    /**
     * Validates and creates the leave request
     *
     * @returns {Promise}
     */
    function createRequest () {
      return vm.request.create()
        .then(triggerChildComponentsSubmitAndWaitForResponse)
        .then(function () {
          postSubmit('LeaveRequest::new');
        });
    }

    /**
     * Sets the "change_balance" attribute to the leave request
     * if a force balance change recalculation is needed on the backend
     */
    function decideIfBalanceChangeNeedsAForceRecalculation () {
      if (isBalanceChangeRecalculationNeeded() && !vm.isRole('staff')) {
        vm.request.change_balance = true;
      }
    }

    /**
     * Deletes the leave request
     */
    function deleteLeaveRequest () {
      dialog.open({
        title: 'Confirm Deletion?',
        copyCancel: 'Cancel',
        copyConfirm: 'Confirm',
        classConfirm: 'btn-danger',
        msg: 'This cannot be undone',
        onConfirm: function () {
          return vm.request.delete()
            .then(function () {
              vm.dismissModal();
              pubSub.publish('LeaveRequest::delete', vm.request);
            });
        }
      });
    }

    /**
     * Close the modal
     */
    function dismissModal () {
      $modalInstance.dismiss({
        $value: 'cancel'
      });
    }

    /**
     * Returns the parameters for to load Absence Type of selected leave type
     *
     * @return {Object}
     */
    function getAbsenceTypeParams () {
      var leaveType = getLeaveType();

      if (leaveType === 'leave') {
        return { is_sick: false };
      } else if (leaveType === 'sickness') {
        return { is_sick: true };
      } else if (leaveType === 'toil') {
        return { allow_accruals_request: true };
      }
    }

    /**
     * Helper functions to get available statuses depending on the
     * current request status value.
     *
     * @return {Array}
     */
    function getAvailableStatusesForCurrentStatus () {
      var currentStatus = vm.getStatusFromValue(vm.request.status_id);

      return getAvailableStatusesForStatusName(currentStatus.name);
    }

    /**
     * Helper function that returns an array of the statuses available
     * for a specific status name.
     *
     * @return {Array}
     */
    function getAvailableStatusesForStatusName (statusName) {
      return _.map(availableStatusesMatrix[statusName], function (status) {
        return vm.requestStatuses[status];
      });
    }

    /**
     * Gets leave type.
     *
     * @return {String} leave type
     */
    function getLeaveType () {
      return vm.request ? vm.request.request_type : (vm.leaveType || null);
    }

    /**
     * Returns an array of statuses depending on the previous status value
     * This is used to populate the dropdown with array of statuses.
     *
     * @return {Array}
     */
    function getStatuses () {
      if (!vm.request || angular.equals({}, vm.requestStatuses)) {
        return [];
      }

      if (!vm.request.status_id) {
        return getAvailableStatusesForStatusName('none');
      }

      return getAvailableStatusesForCurrentStatus();
    }

    /**
     * Gets status object for given status value
     *
     * @param {String} value - value of the status
     * @return {Object} option group of type status or undefined if not found
     */
    function getStatusFromValue (value) {
      return _.find(vm.requestStatuses, function (status) {
        return status.value === value;
      });
    }

    /**
     * Handles errors
     *
     * @param {Array|Object}
     */
    function handleError (errors) {
      vm.errors = _.isArray(errors) ? errors : [errors];
      vm.loading.absenceTypes = false;
      vm.submitting = false;
    }

    /**
     * Checks if a leave request has been changed since opening the modal
     *
     * @return {Boolean}
     */
    function hasRequestChanged () {
      // using angular.equals to automatically ignore the $$hashkey property
      return !angular.equals(
        initialLeaveRequestAttributes,
        vm.request.attributes()
      ) || (vm.canManage && vm.newStatusOnSave);
    }

    /**
     * Initializes after contact is selected either directly or by manager
     *
     * @return {Promise}
     */
    function initAfterContactSelection () {
      vm.postContactSelection = true;
      vm.staffMemberSelectionComplete = false;

      // when manager deselects contact it is called without a selected contact_id
      if (!vm.request.contact_id) {
        return $q.reject('The contact id was not set');
      }

      return $q.resolve()
        .then(loadAbsenceTypes)
        .then(loadEntitlements)
        .then(setAbsenceTypesFromEntitlements)
        .then(setInitialAbsenceType)
        .then(initStatus)
        .then(initContact)
        .then(vm.isMode('edit') ? setInitialAttributes : _.noop)
        .then(function () {
          vm.postContactSelection = false;
          vm.staffMemberSelectionComplete = true;
        })
        .catch(function (error) {
          if (error !== NO_ENTITLEMENT_ERROR) {
            return $q.reject(error);
          }
        });
    }

    /**
     * Initialize available statuses matrix
     */
    function initAvailableStatusesMatrix () {
      var defaultStatuses = [
        sharedSettings.statusNames.moreInformationRequired,
        sharedSettings.statusNames.approved,
        sharedSettings.statusNames.rejected,
        sharedSettings.statusNames.cancelled
      ];

      availableStatusesMatrix['none'] = [
        sharedSettings.statusNames.moreInformationRequired,
        sharedSettings.statusNames.approved
      ];
      availableStatusesMatrix['awaiting_approval'] = defaultStatuses;
      availableStatusesMatrix['more_information_required'] = defaultStatuses;
      availableStatusesMatrix['rejected'] = defaultStatuses;
      availableStatusesMatrix['approved'] = defaultStatuses;
      availableStatusesMatrix['cancelled'] = [
        sharedSettings.statusNames.awaitingApproval
      ].concat(defaultStatuses);
    }

    /**
     * Initialize contact
     *
     * {Promise}
     */
    function initContact () {
      if (vm.canManage) {
        return Contact.find(vm.request.contact_id)
          .then(function (contact) {
            vm.contactName = contact.display_name;
          });
      }

      return $q.resolve();
    }

    /**
     * Initializes the is self record property and sets it to true when
     * on My Leave section and the user is editing their own request or creating
     * a new one for themselves.
     */
    function initIsSelfRecord () {
      var isSectionMyLeave = $rootScope.section === 'my-leave';
      var isMyOwnRequest = +loggedInContactId === +_.get(vm, 'leaveRequest.contact_id');
      var isNewRequest = !_.get(vm, 'leaveRequest.id');

      vm.isSelfRecord = isSectionMyLeave && (isMyOwnRequest || isNewRequest);
    }

    /**
     * Initialises listeners
     */
    function initListeners () {
      listeners.push(
        $rootScope.$on('LeaveRequestPopup::requestObjectUpdated', setInitialAttributes),
        $rootScope.$on('LeaveRequestPopup::absencePeriodChanged', function () {
          loadEntitlements()
            .then(setAbsenceTypesFromEntitlements)
            .then(function () {
              $rootScope.$emit('LeaveRequestPopup::absencePeriodBalancesUpdated', vm.absenceTypes);
            });
        }),
        $rootScope.$on('LeaveRequestPopup::handleError', function (__, errors) {
          handleError(errors);
        }),
        $rootScope.$on('LeaveRequestPopup::childComponent::register', function () {
          childComponentsCount++;
        })
      );

      $scope.$on('$destroy', unsubscribeFromEvents);
      $scope.$on('LeaveRequestPopup::addTab', function (event, tab) {
        tabs.push(tab);
      });
    }

    /**
     * Initializes the leave request object
     */
    function initRequest () {
      var leaveType, attributes;

      vm.request = vm.leaveRequest || null;
      leaveType = getLeaveType();
      attributes = vm.initRequestAttributes();

      if (leaveType === 'leave') {
        vm.request = LeaveRequestInstance.init(attributes);
      } else if (leaveType === 'sickness') {
        vm.request = SicknessRequestInstance.init(attributes);
      } else if (leaveType === 'toil') {
        vm.request = TOILRequestInstance.init(attributes);
      }
    }

    /**
     * Initialize request attributes based on directive
     *
     * @return {Object} attributes
     */
    function initRequestAttributes () {
      var attributes = {};

      // if set indicates self leave request is either being managed or edited
      if (vm.request) {
        attributes = vm.request.attributes();
      } else if (!vm.canManage) {
        attributes = { contact_id: loggedInContactId };
      }

      return attributes;
    }

    /**
     * Initialises the user role to either *admin*, *manager*, or *staff*
     * depending on the user permissions and whether they are managing their own
     * leave or not.
     *
     * @return {Promise}
     */
    function initRoles () {
      role = 'staff';

      // If the user is creating or editing their own leave, they will be
      // treated as a staff regardless of their actual role.
      if (vm.isSelfRecord) {
        return;
      }

      return checkPermissions(sharedSettings.permissions.admin.administer)
        .then(function (isAdmin) {
          isAdmin && (role = 'admin');
        })
        .then(function () {
          // (role === 'staff') means it is not admin so need to check if manager
          return (role === 'staff') && checkPermissions(sharedSettings.permissions.ssp.manage)
            .then(function (isManager) {
              isManager && (role = 'manager');
            });
        })
        .finally(function () {
          vm.canManage = vm.isRole('manager') || vm.isRole('admin');
        });
    }

    /**
     * Initialises status.
     * If a default status is specified, then sets it.
     * If not, sets the status to Approved if user is and admin
     * or a manager who creates a new leave request,
     * otherwise leaves the status unset.
     */
    function initStatus () {
      if (vm.defaultStatus) {
        vm.newStatusOnSave = vm.requestStatuses[sharedSettings.statusNames[vm.defaultStatus]].value;
      } else if (vm.isRole('admin') || (vm.isMode('create') && vm.isRole('manager'))) {
        vm.newStatusOnSave = vm.requestStatuses[sharedSettings.statusNames.approved].value;
      }
    }

    /**
     * Checks if balance change verification is needed.
     * When cancelling or rejecting a request, the balance check is not needed.
     *
     * @return {Boolean}
     */
    function isBalanceChangeRecalculationNeeded () {
      return !vm.request.status_id || !_.includes(['cancelled', 'rejected'], getStatusFromValue(vm.request.status_id).name);
    }

    /**
     * Checks if the leave request has the given status
     *
     * @param {String} leaveStatus
     * @return {Boolean}
     */
    function isLeaveStatus (leaveStatus) {
      var status = vm.getStatusFromValue(vm.request.status_id);

      return status ? status.name === leaveStatus : false;
    }

    /**
     * Checks if popup is opened in given leave type like `leave` or `sickness` or 'toil'
     *
     * @param {String} leaveTypeParam to check the leave type of current request
     * @return {Boolean}
     */
    function isLeaveType (leaveTypeParam) {
      return vm.request && vm.request.request_type === leaveTypeParam;
    }

    /**
     * Checks if popup is opened in given mode
     *
     * @param {String} modeParam to open leave request like edit or view or create
     * @return {Boolean}
     */
    function isMode (modeParam) {
      return vm.mode === modeParam;
    }

    /**
     * Checks if the leave request dates are within the given period
     *
     * @param  {LeaveRequestInstance} request
     * @param  {AbsencePeriodInstance} period
     * @return {Boolean}
     */
    function isRequestInPeriod (request, period) {
      var requestFromDate = moment(request.from_date);
      var requestToDate = moment(request.to_date);

      return (requestFromDate.isSameOrAfter(period.start_date, 'day') &&
        requestToDate.isSameOrBefore(period.end_date, 'day'));
    }

    /**
     * Checks if popup is opened in given role
     *
     * @param {String} roleParam like manager, staff
     * @return {Boolean}
     */
    function isRole (roleParam) {
      return role === roleParam;
    }

    /**
     * Loads all absence periods
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all()
        .then(function (periods) {
          vm.absencePeriods = periods;
        });
    }

    /**
     * Initializes values for absence types and entitlements when the
     * leave request popup model is displayed
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all(getAbsenceTypeParams())
        .then(AbsenceType.loadCalculationUnits)
        .then(function (absenceTypes) {
          absenceTypesAndIds = {
            types: absenceTypes,
            ids: absenceTypes.map(function (absenceType) {
              return absenceType.id;
            })
          };
        });
    }

    /**
     * Loads entitlements for the current contact
     * for the available absence types and the selected absence period
     *
     * @return {Promise}
     */
    function loadEntitlements () {
      vm.loading.entitlements = true;

      return Entitlement.all({
        contact_id: vm.request.contact_id,
        period_id: vm.period.id,
        type_id: { IN: absenceTypesAndIds.ids }
      }, true) // `true` because we want to use the 'future' balance for calculation
        .finally(function () {
          vm.loading.entitlements = false;
        });
    }

    /**
     * Loads the contact id of the currently logged in user
     *
     * @return {Promise}
     */
    function loadLoggedInContactId () {
      return Session.get().then(function (value) {
        loggedInContactId = value.contactId;
      });
    }

    /**
     * Loads the managees of currently logged in user
     *
     * @return {Promise}
     */
    function loadManagees () {
      if (vm.selectedContactId) {
        // In case of a pre-selected contact administration
        return Contact.find(vm.selectedContactId)
          .then(function (contact) {
            vm.managedContacts = [contact];
          });
      } else if (vm.isRole('admin')) {
        // In case of general administration
        return Contact.all()
          .then(function (contacts) {
            vm.managedContacts = _.remove(contacts.list, function (contact) {
              // Removes the admin from the list of contacts
              return contact.id !== loggedInContactId;
            });
          });
      } else {
        // In any other case (including managing)
        return Contact.find(loggedInContactId)
          .then(function (contact) {
            return contact.leaveManagees();
          })
          .then(function (contacts) {
            vm.managedContacts = contacts;
          });
      }
    }

    /**
     * Initializes leave request statuses
     *
     * @return {Promise}
     */
    function loadStatuses () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          vm.requestStatuses = _.indexBy(statuses, 'name');
        });
    }

    /**
     * Maps absence types to be more compatible for UI selection
     *
     * @param {Array} absenceTypes
     * @param {Object} entitlements
     * @return {Array} of filtered absence types for given entitlements
     */
    function mapAbsenceTypesWithBalance (absenceTypes, entitlements) {
      var entitlement;

      return _.compact(absenceTypes.map(function (absenceType) {
        entitlement = _.find(entitlements, { type_id: absenceType.id });

        if (!entitlement) {
          return;
        }

        return {
          id: entitlement.type_id,
          title: absenceType.title + ' ( ' + entitlement.remainder.current + ' ) ',
          remainder: entitlement.remainder.current,
          allow_overuse: absenceType.allow_overuse,
          calculation_unit_name: absenceType.calculation_unit_name
        };
      }));
    }

    /**
     * Called after successful submission of leave request
     *
     * @param {String} eventName name of the event to emit
     */
    function postSubmit (eventName) {
      broadcastRequestUpdatedEvent();
      pubSub.publish(eventName, vm.request);

      vm.errors = [];

      vm.dismissModal();
    }

    /**
     * Reverts the request from and to dates and times back to the original values
     */
    function revertRequestOriginalDatesAndTimes () {
      ['from', 'to'].forEach(function (dateType) {
        vm.request[dateType + '_date'] =
          initialLeaveRequestAttributes[dateType + '_date'];
      });
    }

    /**
     * Sets entitlements and sets the absences type available for the user.
     * It depends on absenceTypesAndIds to be set to list of absence types and ids
     *
     * @param  {Array} entitlements collection of entitlements instances
     * @return {Promise}
     */
    function setAbsenceTypesFromEntitlements (entitlements) {
      vm.absenceTypes = mapAbsenceTypesWithBalance(absenceTypesAndIds.types, entitlements);

      if (!vm.absenceTypes.length) {
        return $q.reject(NO_ENTITLEMENT_ERROR);
      }
    }

    /**
     * Sets initial Absence Period depending on the mode.
     * If a request is being created, the current period is selected.
     * Otherwise, the period belonding to the request is selected.
     */
    function setInitialAbsencePeriod () {
      vm.period = _.find(vm.absencePeriods, function (period) {
        return (vm.isMode('create')
          ? period.current
          : isRequestInPeriod(vm.request, period));
      });
    }

    /**
     * Sets initial Absence Type depending on the mode.
     * It assigns the first absence type to the leave request in "create" mode,
     * otherwise, it sets the absence type of the current leave request
     */
    function setInitialAbsenceType () {
      if (vm.isMode('create')) {
        vm.selectedAbsenceType = vm.absenceTypes[0];
        vm.request.type_id = vm.selectedAbsenceType.id;
      } else {
        vm.selectedAbsenceType = _.find(vm.absenceTypes, function (absenceType) {
          return absenceType.id === vm.request.type_id;
        });
      }
    }

    /**
     * Set Initial attribute
     */
    function setInitialAttributes () {
      initialLeaveRequestAttributes = angular.copy(vm.request.attributes());
    }

    /**
     * Sets modal mode to "create" in case of no leave request or
     * to "edit" or "view" depending on the status of the leave request
     */
    function setModalMode () {
      var viewModeStatuses;

      if (vm.request.id) {
        viewModeStatuses = [
          vm.requestStatuses[sharedSettings.statusNames.approved].value,
          vm.requestStatuses[sharedSettings.statusNames.adminApproved].value,
          vm.requestStatuses[sharedSettings.statusNames.rejected].value,
          vm.requestStatuses[sharedSettings.statusNames.cancelled].value
        ];
        vm.mode = 'edit';

        if (vm.isRole('staff') && viewModeStatuses.indexOf(vm.request.status_id) > -1) {
          vm.mode = 'view';
        }
      } else {
        vm.mode = 'create';
      }
    }

    /**
     * Submits the form, only if the leave request is valid, also emits event
     * to notify event subscribers about the the save.
     * Updates request based on role and mode
     */
    function submit () {
      var originalStatus = vm.request.status_id;

      if (vm.isMode('view') || vm.submitting) {
        return;
      }

      vm.submitting = true;

      changeStatusBeforeSave();
      amendRequestParamsBeforeSave();

      return vm.request.isValid()
        .then(isBalanceChangeRecalculationNeeded() && checkIfBalanceChangeHasChanged)
        .then(decideIfBalanceChangeNeedsAForceRecalculation)
        .then(checkIfRequestDatesAndTimesNeedToBeReverted() && revertRequestOriginalDatesAndTimes)
        .then(submitAllTabs)
        .then(function () {
          return vm.isMode('edit') ? updateRequest() : createRequest();
        })
        .catch(function (errors) {
          // if there is an error, put back the original status
          vm.request.status_id = originalStatus;
          errors && handleError(errors);
        })
        .finally(function () {
          vm.submitting = false;
        });
    }

    /**
     * Submits all tabs and returns a promise of the result.
     *
     * @return {Promise}
     */
    function submitAllTabs () {
      return $q.all(tabs.map(function (tab) {
        return tab.onBeforeSubmit && tab.onBeforeSubmit();
      }));
    }

    /**
     * Fire an event to start child processes which needs to be done after leave request is saved.
     * Waits for the response before resolving the promise
     *
     * @returns {Promise}
     */
    function triggerChildComponentsSubmitAndWaitForResponse () {
      var deferred = $q.defer();
      var errors = [];
      var responses = 0;

      if (childComponentsCount > 0) {
        $rootScope.$broadcast('LeaveRequestPopup::submit', doneCallback);
      } else {
        deferred.resolve();
      }

      function doneCallback (error) {
        error && errors.push(error);

        if (++responses === childComponentsCount) {
          errors.length > 0 ? deferred.reject(errors) : deferred.resolve();
        }
      }

      return deferred.promise;
    }

    /**
     * Unsubscribes from events
     */
    function unsubscribeFromEvents () {
      listeners.forEach(function (listener) {
        listener();
      });
    }

    /**
     * Broadcast an event to notify that the selected absence type has been changed
     */
    function updateAbsenceType () {
      $rootScope.$broadcast('LeaveRequestPopup::absenceTypeChanged');
    }

    /**
     * Validates and updates the leave request
     *
     * @returns {Promise}
     */
    function updateRequest () {
      return vm.request.update()
        .then(triggerChildComponentsSubmitAndWaitForResponse)
        .then(function () {
          if (vm.isRole('manager')) {
            postSubmit('LeaveRequest::updatedByManager');
          } else if (vm.isRole('staff') || vm.isRole('admin')) {
            postSubmit('LeaveRequest::edit');
          }
        });
    }
  }
});
