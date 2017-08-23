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
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/public-holiday-model',
  'leave-absences/shared/instances/leave-request.instance',
  'leave-absences/shared/instances/sickness-request.instance',
  'leave-absences/shared/instances/toil-request.instance'
], function (angular, controllers, _) {
  'use strict';

  controllers.controller('RequestCtrl', RequestCtrl);

  RequestCtrl.$inject = [
    '$log', '$q', '$rootScope', '$uibModalInstance', 'checkPermissions', 'api.optionGroup', 'dialog', 'pubSub', 'directiveOptions', 'Contact',
    'Session', 'AbsencePeriod', 'AbsenceType', 'Entitlement', 'LeaveRequest', 'LeaveRequestInstance', 'shared-settings', 'SicknessRequestInstance',
    'TOILRequestInstance'
  ];

  function RequestCtrl ($log, $q, $rootScope, $modalInstance, checkPermissions, OptionGroup, dialog, pubSub, directiveOptions, Contact, Session,
    AbsencePeriod, AbsenceType, Entitlement, LeaveRequest, LeaveRequestInstance, sharedSettings, SicknessRequestInstance, TOILRequestInstance) {
    $log.debug('RequestCtrl');

    var absenceTypesAndIds;
    var availableStatusesMatrix = {};
    var childComponentsCount = 0;
    var initialLeaveRequestAttributes = {}; // used to compare the change in leaverequest in edit mode
    var listeners = [];
    var loggedInContactId = '';
    var NO_ENTITLEMENT_ERROR = 'No entitlement';
    var role = '';
    var vm = _.merge(this, directiveOptions);

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.balance = {
      closing: 0,
      opening: 0,
      change: {
        amount: 0,
        breakdown: []
      }
    };
    vm.canManage = false; // vm flag is set on initialisation of the controller
    vm.contactName = null; // contact name of the owner of leave request
    vm.errors = [];
    vm.fileUploader = null;
    vm.loading = { absenceTypes: true };
    vm.managedContacts = [];
    vm.mode = ''; // can be edit, create, view
    vm.newStatusOnSave = null;
    vm.period = {};
    vm.postContactSelection = false; // flag to track if user is selected for enabling UI
    vm.requestStatuses = {};
    vm.selectedAbsenceType = {};
    vm.submitting = false;

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
    vm._loadAbsenceTypes = _loadAbsenceTypes;

    /**
     * Initializes the controller on loading the dialog
     *
     * @return {Promise}
     */
    (function init () {
      vm.loading.absenceTypes = true;
      initAvailableStatusesMatrix();
      initListeners();

      return $q.all([
        loadLoggedInContactId(),
        initRoles(),
        loadAbsencePeriods(),
        loadStatuses()
      ]).then(function () {
        return $q.all([
          initAbsencePeriod(),
          initRequest()
        ]);
      })
      .then(function () {
        initOpenMode();

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
     * Checks if submit button can be enabled for user and returns true if succeeds
     *
     * @return {Boolean}
     */
    function canSubmit () {
      var canSubmit = vm.checkSubmitConditions ? vm.checkSubmitConditions() : false;

      // check if user has changed any attribute
      if (vm.isMode('edit')) {
        canSubmit = canSubmit && hasRequestChanged();
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
              $rootScope.$emit('LeaveRequest::deleted', vm.request);
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
     * @param {String} leaveType - leave type, it is passed only for new requests
     * @param {LeaveRequestInstance} request leave request for edit calls

     * @return {String} leave type
     */
    function getLeaveType () {
      return vm.request ? vm.request.request_type : (vm.leaveType || null);
    }

    /**
     * Gets currently selected absence type from leave request type_id
     *
     * @return {Object} absence type object
     */
    function getSelectedAbsenceType () {
      return _.find(vm.absenceTypes, function (absenceType) {
        return absenceType.id === vm.request.type_id;
      });
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
        ) || (vm.fileUploader && vm.fileUploader.queue.length !== 0) ||
        (vm.canManage && vm.newStatusOnSave);
    }

    /**
     * Inits absence period for the current date
     */
    function initAbsencePeriod () {
      vm.period = _.find(vm.absencePeriods, function (period) {
        return period.current;
      });
    }

    /**
     * Initializes after contact is selected either directly or by manager
     *
     * @return {Promise}
     */
    function initAfterContactSelection () {
      vm.postContactSelection = true;

      // when manager deselects contact it is called without a selected contact_id
      if (!vm.request.contact_id) {
        return $q.reject('The contact id was not set');
      }

      return $q.all([
        vm._loadAbsenceTypes()
      ])
      .then(function () {
        setInitialAbsenceTypes();
        initStatus();
        initContact();

        if (vm.isMode('edit')) {
          setInitialAttributes();
        }

        vm.postContactSelection = false;
        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
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
     * Initialises listeners
     */
    function initListeners () {
      listeners.push(
        $rootScope.$on('LeaveRequestPopup::requestObjectUpdated', setInitialAttributes),
        $rootScope.$on('LeaveRequestPopup::handleError', function (__, errors) {
          handleError(errors);
        }),
        $rootScope.$on('LeaveRequestPopup::childComponent::register', function () {
          childComponentsCount++;
        })
      );
    }

    /**
     * Initialises open mode of the dialog
     */
    function initOpenMode () {
      if (vm.request.id) {
        vm.mode = 'edit';

        var viewModeStatuses = [
          vm.requestStatuses[sharedSettings.statusNames.approved].value,
          vm.requestStatuses[sharedSettings.statusNames.adminApproved].value,
          vm.requestStatuses[sharedSettings.statusNames.rejected].value,
          vm.requestStatuses[sharedSettings.statusNames.cancelled].value
        ];

        if (vm.isRole('staff') && viewModeStatuses.indexOf(vm.request.status_id) > -1) {
          vm.mode = 'view';
        }
      } else {
        vm.mode = 'create';
      }
    }

    function initRequest () {
      vm.request = vm.leaveRequest || null;
      var leaveType = getLeaveType();
      var attributes = vm.initRequestAttributes();

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
        // _.deepClone or angular.copy were not uploading files correctly
        attributes = vm.request.attributes();
      } else if (!vm.canManage) {
        attributes = {contact_id: loggedInContactId};
      }

      return attributes;
    }

    /**
     * Initialises roles
     */
    function initRoles () {
      role = 'staff';

      return checkPermissions(sharedSettings.permissions.admin.administer)
        .then(function (isAdmin) {
          role = isAdmin ? 'admin' : role;
        })
        .then(function () {
          // (role === 'staff') means it is not admin so need to check if manager
          return (role === 'staff') && checkPermissions(sharedSettings.permissions.ssp.manage)
              .then(function (isManager) {
                role = isManager ? 'manager' : role;
              });
        })
        .finally(function () {
          vm.canManage = vm.isRole('manager') || vm.isRole('admin');
        });
    }

    /**
     * Initialises status
     */
    function initStatus () {
      if (vm.isRole('admin') || (vm.isMode('create') && vm.isRole('manager'))) {
        vm.newStatusOnSave = vm.requestStatuses[sharedSettings.statusNames.approved].value;
      }
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
      return vm.request.request_type === leaveTypeParam;
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
      return entitlements.map(function (entitlementItem) {
        var absenceType = _.find(absenceTypes, function (absenceTypeItem) {
          return absenceTypeItem.id === entitlementItem.type_id;
        });

        return {
          id: entitlementItem.type_id,
          title: absenceType.title + ' ( ' + entitlementItem.remainder.current + ' ) ',
          remainder: entitlementItem.remainder.current,
          allow_overuse: absenceType.allow_overuse
        };
      });
    }

    /**
     * Called after successful submission of leave request
     *
     * @param {String} eventName name of the event to emit
     */
    function postSubmit (eventName) {
      broadcastRequestUpdatedEvent();
      $rootScope.$emit(eventName, vm.request);

      vm.errors = [];
      // close the modal
      vm.dismissModal();
    }

    /**
     * Sets entitlements and sets the absences type available for the user.
     * It depends on absenceTypesAndIds to be set to list of absence types and ids
     *
     * @param {Object} absenceTypesAndIds contains all absencetypes and their ids
     * @return {Promise}
     */
    function setAbsenceTypesFromEntitlements (absenceTypesAndIds) {
      return Entitlement.all({
        contact_id: vm.request.contact_id,
        period_id: vm.period.id,
        type_id: {IN: absenceTypesAndIds.ids}
      }, true) // `true` because we want to use the 'future' balance for calculation
        .then(function (entitlements) {
          // create a list of absence types with a `balance` property
          vm.absenceTypes = mapAbsenceTypesWithBalance(absenceTypesAndIds.types, entitlements);
          if (!vm.absenceTypes.length) {
            return $q.reject(NO_ENTITLEMENT_ERROR);
          }
        });
    }

    /**
     * Set initial values to absence types when opening the popup
     */
    function setInitialAbsenceTypes () {
      if (vm.isMode('create')) {
        // Assign the first absence type to the leave request
        vm.selectedAbsenceType = vm.absenceTypes[0];
        vm.request.type_id = vm.selectedAbsenceType.id;
      } else {
        // Either View or Edit Mode
        vm.selectedAbsenceType = getSelectedAbsenceType();
      }
    }

    /**
     * Set Initial attribute
     */
    function setInitialAttributes () {
      initialLeaveRequestAttributes = angular.copy(vm.request.attributes());
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

      return validateBeforeSubmit()
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
          unsubscribeFromEvents();

          errors.length > 0 ? deferred.reject(errors) : deferred.resolve();
        }
      }

      return deferred.promise;
    }

    /**
     * Gets called when the component is destroyed
     */
    function unsubscribeFromEvents () {
      // destroy all the event
      _.forEach(listeners, function (listener) {
        listener();
      });
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

    /**
     * Validates a Leave request before submitting
     *
     * @returns {Promise}
     */
    function validateBeforeSubmit () {
      if (vm.balance.closing < 0 && vm.selectedAbsenceType.allow_overuse === '0') {
        // show an error
        return $q.reject(['You cannot make a request for vm leave type at vm time ' +
        'as vm would leave you with a negative balance']);
      }

      return vm.request.isValid();
    }

    /**
     * Initializes values for absence types and entitlements when the
     * leave request popup model is displayed
     *
     * @return {Promise}
     */
    function _loadAbsenceTypes () {
      return AbsenceType.all(getAbsenceTypeParams())
        .then(function (absenceTypes) {
          var absenceTypesIds = absenceTypes.map(function (absenceType) {
            return absenceType.id;
          });

          absenceTypesAndIds = {
            types: absenceTypes,
            ids: absenceTypesIds
          };

          return setAbsenceTypesFromEntitlements(absenceTypesAndIds);
        });
    }
  }
});
