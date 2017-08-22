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
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/public-holiday-model'
], function (angular, controllers, _, moment) {
  'use strict';

  controllers.controller('RequestCtrl', RequestCtrl);

  RequestCtrl.$inject = ['$log', '$q', '$rootScope', 'Contact', 'dialog',
    'AbsencePeriod', 'AbsenceType', 'api.optionGroup', 'checkPermissions',
    'pubSub', 'Calendar', 'Entitlement', 'HR_settings', 'Session',
    'LeaveRequest', 'PublicHoliday', 'shared-settings'];

  function RequestCtrl ($log, $q, $rootScope, Contact, dialog, AbsencePeriod,
    AbsenceType, OptionGroup, checkPermissions, pubSub, Calendar, Entitlement,
    HRSettings, Session, LeaveRequest, PublicHoliday, sharedSettings) {
    $log.debug('RequestCtrl');

    var absenceTypesAndIds;
    var availableStatusesMatrix = {};
    var childComponentsCount = 0;
    var initialLeaveRequestAttributes = {}; // used to compare the change in leaverequest in edit mode
    var listeners = [];
    var loggedInContactId = '';
    var role = '';
    var vm = this;
    var NO_ENTITLEMENT_ERROR = 'No entitlement';

    this.absencePeriods = [];
    this.absenceTypes = [];
    this.calendar = {};
    this.canManage = false; // this flag is set on initialisation of the controller
    this.contactName = null;
    this.errors = [];
    this.fileUploader = null;
    this.isSelfRecord = false; // this flag is set on initialisation of the controller
    this.managedContacts = [];
    this.mode = ''; // can be edit, create, view
    this.newStatusOnSave = null;
    this.period = {};
    this.postContactSelection = false; // flag to track if user is selected for enabling UI
    this.requestDayTypes = [];
    this.requestStatuses = {};
    this.selectedAbsenceType = {};
    this.statusNames = sharedSettings.statusNames;
    this.submitting = false;
    this.balance = {
      closing: 0,
      opening: 0,
      change: {
        amount: 0,
        breakdown: []
      }
    };
    this.loading = {
      absenceTypes: true,
      showBalanceChange: false,
      fromDayTypes: false,
      toDayTypes: false
    };
    this.pagination = {
      currentPage: 1,
      filteredbreakdown: this.balance.change.breakdown,
      numPerPage: 7,
      totalItems: this.balance.change.breakdown.length,
      pageChanged: pageChanged
    };
    this.uiOptions = {
      isChangeExpanded: false,
      multipleDays: true,
      userDateFormat: HRSettings.DATE_FORMAT,
      showBalance: false,
      date: {
        from: {
          show: false,
          options: {
            startingDay: 1,
            showWeeks: false
          }
        },
        to: {
          show: false,
          options: {
            minDate: null,
            maxDate: null,
            startingDay: 1,
            showWeeks: false
          }
        },
        expiry: {
          show: false,
          options: {
            minDate: null,
            maxDate: null,
            startingDay: 1,
            showWeeks: false
          }
        }
      }
    };

    this.changeInNoOfDays = changeInNoOfDays;
    this.calculateBalanceChange = calculateBalanceChange;
    this.canSubmit = canSubmit;
    this.closeAlert = closeAlert;
    this.deleteLeaveRequest = deleteLeaveRequest;
    this.dismissModal = dismissModal;
    this.formatDateTime = formatDateTime;
    this.getStatuses = getStatuses;
    this.getStatusFromValue = getStatusFromValue;
    this.initAfterContactSelection = initAfterContactSelection;
    this.isLeaveStatus = isLeaveStatus;
    this.isLeaveType = isLeaveType;
    this.isMode = isMode;
    this.isRole = isRole;
    this.ok = ok;
    this.submit = submit;
    this.loadAbsencePeriodDatesTypes = loadAbsencePeriodDatesTypes;
    this.updateAbsencePeriodDatesTypes = updateAbsencePeriodDatesTypes;
    this.updateBalance = updateBalance;
    this._calculateOpeningAndClosingBalance = _calculateOpeningAndClosingBalance;
    this._checkAndSetAbsencePeriod = _checkAndSetAbsencePeriod;
    this._convertDateToServerFormat = _convertDateToServerFormat;
    this._convertDateFormatFromServer = _convertDateFormatFromServer;
    this._init = _init;
    this._initRequestAttributes = _initRequestAttributes;
    this._loadCalendar = _loadCalendar;
    this._loadAbsenceTypes = _loadAbsenceTypes;
    this._reset = _reset;
    this._setDates = _setDates;
    this._setDateAndTypes = _setDateAndTypes;
    this._setMinMaxDate = _setMinMaxDate;

    /**
     * Broadcasts an event when request has been updated from awaiting approval status to something else
     */
    function broadcastRequestUpdatedEvent () {
      var awaitingApprovalStatusValue = this.requestStatuses[sharedSettings.statusNames.awaitingApproval].value;

      // Check if the leave request had awaiting approval status before update,
      // and after update the status is not awaiting approval
      if (initialLeaveRequestAttributes.status_id === awaitingApprovalStatusValue &&
        awaitingApprovalStatusValue !== this.request.status_id) {
        pubSub.publish('ManagerBadge:: Update Count');
      }
    }

    /**
     * Calculate change in balance, it updates local balance variables.
     *
     * @return {Promise} empty promise if all required params are not set otherwise promise from server
     */
    function calculateBalanceChange () {
      var self = this;

      self._setDateAndTypes();

      if (!canCalculateChange.call(self)) {
        return $q.resolve();
      }

      self.loading.showBalanceChange = true;
      return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange.call(self))
        .then(function (balanceChange) {
          if (balanceChange) {
            self.balance.change = balanceChange;
            self._calculateOpeningAndClosingBalance();
            rePaginate.call(self);
          }
          self.loading.showBalanceChange = false;
        })
        .catch(handleError.bind(self));
    }

    /**
     * Checks if all params are set to calculate balance
     *
     * @param {Boolean} true if all present else false
     */
    function canCalculateChange () {
      return !!this.request.from_date && !!this.request.to_date &&
        !!this.request.from_date_type && !!this.request.to_date_type;
    }

    /**
     * Checks if submit button can be enabled for user and returns true if succeeds
     *
     * @return {Boolean}
     */
    function canSubmit () {
      var canSubmit = canCalculateChange.call(this);

      // check if user has changed any attribute
      if (this.isMode('edit')) {
        canSubmit = canSubmit && hasRequestChanged.call(this);
      }

      // check if manager has changed status
      if (this.canManage && this.requestStatuses) {
        // awaiting_approval will not be available in this.requestStatuses if manager has changed selection
        canSubmit = canSubmit && !!this.getStatusFromValue(this.newStatusOnSave);
      }

      // check if the selected date period is in absence period
      canSubmit = canSubmit && !!this.period.id;

      return canSubmit && !this.isMode('view');
    }

    /**
     * Change handler when changing no. of days like Multiple Days or Single Day.
     * It will reset dates, day types, change balance.
     */
    function changeInNoOfDays () {
      this._reset();
      this._calculateOpeningAndClosingBalance();
    }

    /**
     * Changes status of the leave request before saving it
     * When recording for yourself the status_id should be always set to awaitingApproval before saving
     * If manager or admin have changed the status through dropdown, assign the same before calling API
     */
    function changeStatusBeforeSave () {
      if (this.isSelfRecord) {
        this.request.status_id = this.requestStatuses[sharedSettings.statusNames.awaitingApproval].value;
      } else if (this.canManage) {
        this.request.status_id = this.newStatusOnSave || this.request.status_id;
      }
    }

    /**
    * Closes the error alerts if any
    */
    function closeAlert () {
      this.errors = [];
    }

    /**
     * Validates and creates the leave request
     *
     * @returns {Promise}
     */
    function createRequest () {
      return this.request.create()
        .then(triggerChildComponentsSubmitAndWaitForResponse)
        .then(function () {
          postSubmit.call(this, 'LeaveRequest::new');
        }.bind(this));
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
          return this.directiveOptions.leaveRequest.delete()
            .then(function () {
              this.dismissModal();
              $rootScope.$emit('LeaveRequest::deleted', this.directiveOptions.leaveRequest);
            }.bind(this));
        }.bind(this)
      });
    }

    /**
     * Close the modal
     */
    function dismissModal () {
      this.$modalInstance.dismiss({
        $value: 'cancel'
      });
    }

    /**
     * This method will be used on the view to return a list of available
     * leave request day types (All day, Half-day AM, Half-day PM, Non working day,
     * Weekend, Public holiday) for the given date (which is the date
     * selected by the user via datepicker)
     *
     * If no date is passed, then no list is returned
     *
     * @param  {Date} date
     * @param  {String} dayType - set to from if from date is selected else to
     * @return {Promise} resolves with an array of day types
     */
    function filterLeaveRequestDayTypes (date, dayType) {
      var listToReturn;

      if (!date) {
        return $q.reject([]);
      }

      // Make a copy of the list
      listToReturn = this.requestDayTypes.slice(0);
      date = this._convertDateToServerFormat(date);

      return PublicHoliday.isPublicHoliday(date)
        .then(function (result) {
          if (result) {
            return listToReturn.filter(function (publicHoliday) {
              return publicHoliday.name === 'public_holiday';
            });
          }

          return getDayTypesFromDate.call(this, date, listToReturn)
            .then(function (inCalendarList) {
              return inCalendarList.length
                ? inCalendarList
                : listToReturn.filter(function (dayType) {
                  return _.includes(['all_day', 'half_day_am', 'half_day_pm'], dayType.name);
                });
            });
        }.bind(this))
        .then(function (listToReturn) {
          setDayType.call(this, dayType, listToReturn);

          return listToReturn;
        }.bind(this));
    }

    /**
     * Format a date-time into user format and returns
     *
     * @return {String}
     */
    function formatDateTime (dateTime) {
      return moment(dateTime, sharedSettings.serverDateTimeFormat).format(this.uiOptions.userDateFormat.toUpperCase() + ' HH:mm');
    }

    /**
     * Helper functions to get available statuses depending on the
     * current request status value.
     *
     * @return {Array}
     */
    function getAvailableStatusesForCurrentStatus () {
      var currentStatus = this.getStatusFromValue(this.request.status_id);

      return getAvailableStatusesForStatusName.call(this, currentStatus.name);
    }

    /**
     * Helper function that returns an array of the statuses available
     * for a specific status name.
     *
     * @return {Array}
     */
    function getAvailableStatusesForStatusName (statusName) {
      return _.map(availableStatusesMatrix[statusName], function (status) {
        return this.requestStatuses[status];
      }.bind(this));
    }

    /**
     * Gets list of day types if its found to be weekend or non working in calendar
     *
     * @param {Date} date to Checks
     * @param {Array} listOfDayTypes array of day types
     * @return {Promise} resolves with an {Array} of day types
     */
    function getDayTypesFromDate (date, listOfDayTypes) {
      date = moment(date);

      return $q.all([
        this.calendar.isNonWorkingDay(date),
        this.calendar.isWeekend(date)
      ])
      .then(function (results) {
        return results[0] ? 'non_working_day' : (results[1] ? 'weekend' : null);
      })
      .then(function (nameFilter) {
        return !nameFilter ? [] : listOfDayTypes.filter(function (day) {
          return day.name === nameFilter;
        });
      });
    }

    /**
     * Helper function to obtain params for leave request calculateBalanceChange api call
     *
     * @return {Object} containing required keys for leave request
     */
    function getParamsForBalanceChange () {
      return _.pick(this.request, ['contact_id', 'from_date',
        'from_date_type', 'to_date', 'to_date_type'
      ]);
    }

    /**
     * Gets currently selected absence type from leave request type_id
     *
     * @return {Object} absence type object
     */
    function getSelectedAbsenceType () {
      return _.find(this.absenceTypes, function (absenceType) {
        return absenceType.id === this.request.type_id;
      }.bind(this));
    }

    /**
     * Returns an array of statuses depending on the previous status value
     * This is used to populate the dropdown with array of statuses.
     *
     * @return {Array}
     */
    function getStatuses () {
      if (!this.request || angular.equals({}, this.requestStatuses)) {
        return [];
      }

      if (!this.request.status_id) {
        return getAvailableStatusesForStatusName.call(this, 'none');
      }

      return getAvailableStatusesForCurrentStatus.call(this);
    }

    /**
     * Gets status object for given status value
     *
     * @param {String} value - value of the status
     * @return {Object} option group of type status or undefined if not found
     */
    function getStatusFromValue (value) {
      return _.find(this.requestStatuses, function (status) {
        return status.value === value;
      });
    }

    /**
     * Handles errors
     *
     * @param {Array|Object}
     */
    function handleError (errors) {
      this.errors = _.isArray(errors) ? errors : [errors];
      this.loading.showBalanceChange = false;
      this.loading.absenceTypes = false;
      this.loading.fromDayTypes = false;
      this.loading.toDayTypes = false;
      this.submitting = false;
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
        this.request.attributes()
      ) || (this.fileUploader && this.fileUploader.queue.length !== 0) ||
        (this.canManage && this.newStatusOnSave);
    }

    /**
     * Inits absence period for the current date
     */
    function initAbsencePeriod () {
      this.period = _.find(this.absencePeriods, function (period) {
        return period.current;
      });
    }

    /**
     * Initializes after contact is selected either directly or by manager
     *
     * @return {Promise}
     */
    function initAfterContactSelection () {
      var self = this;
      self.postContactSelection = true;

      // when manager deselects contact it is called without a selected contact_id
      if (!self.request.contact_id) {
        return $q.reject('The contact id was not set');
      }

      return $q.all([
        self._loadAbsenceTypes(),
        self._loadCalendar()
      ])
        .then(function () {
          return loadDayTypes.call(self);
        })
        .then(function () {
          return initDates.call(self);
        })
        .then(function () {
          setInitialAbsenceTypes.call(self);
          initStatus.call(self);
          initContact.call(self);

          if (self.isMode('edit')) {
            setInitialAttributes.call(self);
          }

          self.postContactSelection = false;
          return self.calculateBalanceChange();
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
      if (this.canManage) {
        return Contact.find(this.request.contact_id)
          .then(function (contact) {
            this.contactName = contact.display_name;
          }.bind(this));
      }

      return $q.resolve();
    }

    /**
     * Initialize from and to dates and day types.
     * It will also set the day types.
     *
     * @return {Promise}
     */
    function initDates () {
      if (!this.isMode('create')) {
        var attributes = this.request.attributes();

        this.uiOptions.fromDate = this._convertDateFormatFromServer(this.request.from_date);

        return this.loadAbsencePeriodDatesTypes(this.uiOptions.fromDate, 'from')
          .then(function () {
            // to_date and type has been reset in above call so reinitialize from clone
            this.request.to_date = attributes.to_date;
            this.request.to_date_type = attributes.to_date_type;
            this.uiOptions.toDate = this._convertDateFormatFromServer(this.request.to_date);
            return this.loadAbsencePeriodDatesTypes(this.uiOptions.toDate, 'to');
          }.bind(this));
      } else {
        return $q.resolve();
      }
    }

    /**
     * Initialises listeners
     */
    function initListeners () {
      listeners.push(
        $rootScope.$on('LeaveRequestPopup::requestObjectUpdated', setInitialAttributes.bind(this)),
        $rootScope.$on('LeaveRequestPopup::childComponent::register', function () { childComponentsCount++; })
      );
    }

    /**
     * Initialises open mode of the dialog
     */
    function initOpenMode () {
      if (this.request.id) {
        this.mode = 'edit';

        var viewModeStatuses = [
          this.requestStatuses[sharedSettings.statusNames.approved].value,
          this.requestStatuses[sharedSettings.statusNames.adminApproved].value,
          this.requestStatuses[sharedSettings.statusNames.rejected].value,
          this.requestStatuses[sharedSettings.statusNames.cancelled].value
        ];

        if (this.isRole('staff') && viewModeStatuses.indexOf(this.request.status_id) > -1) {
          this.mode = 'view';
        }
      } else {
        this.mode = 'create';
      }
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
        this.canManage = this.isRole('manager') || this.isRole('admin');
        this.isSelfRecord = this.directiveOptions.isSelfRecord;
      }.bind(this));
    }

    /**
     * Initialises status
     */
    function initStatus () {
      if (this.isRole('admin') || (this.isMode('create') && this.isRole('manager'))) {
        this.newStatusOnSave = this.requestStatuses[sharedSettings.statusNames.approved].value;
      }
    }

    /**
     * Checks if the leave request has the given status
     *
     * @param {String} leaveStatus
     * @return {Boolean}
     */
    function isLeaveStatus (leaveStatus) {
      var status = this.getStatusFromValue(this.request.status_id);

      return status ? status.name === leaveStatus : false;
    }

    /**
     * Checks if popup is opened in given leave type like `leave` or `sickness` or 'toil'
     *
     * @param {String} leaveTypeParam to check the leave type of current request
     * @return {Boolean}
     */
    function isLeaveType (leaveTypeParam) {
      return this.request.request_type === leaveTypeParam;
    }

    /**
     * Checks if popup is opened in given mode
     *
     * @param {String} modeParam to open leave request like edit or view or create
     * @return {Boolean}
     */
    function isMode (modeParam) {
      return this.mode === modeParam;
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
     * Loads absence types and calendar data on component initialization and
     * when they need to be updated.
     *
     * @param {Date} date - the selected date
     * @param {String} dayType - set to from if from date is selected else to
     * @return {Promise}
     */
    function loadAbsencePeriodDatesTypes (date, dayType) {
      var oldPeriodId = this.period.id;
      dayType = dayType || 'from';
      this.loading[dayType + 'DayTypes'] = true;

      return this._checkAndSetAbsencePeriod(date)
        .then(function () {
          var isInCurrentPeriod = oldPeriodId === this.period.id;

          if (!isInCurrentPeriod) {
            // partial reset is required when user has selected a to date and
            // then changes absence period from from date
            // no reset required for single days and to date changes
            if (this.uiOptions.multipleDays && dayType === 'from') {
              this.uiOptions.showBalance = false;
              this.uiOptions.toDate = null;
              this.request.to_date = null;
              this.request.to_date_type = null;
            }

            return $q.all([
              this._loadAbsenceTypes(),
              this._loadCalendar()
            ]);
          }
        }.bind(this))
        .then(function () {
          this._setMinMaxDate();

          return filterLeaveRequestDayTypes.call(this, date, dayType);
        }.bind(this))
        .finally(function () {
          /**
           * after the request is completed fromDayTypes or toDayTypes are
           * set to false and the corresponding field is shown on the ui.
           */
          this.loading[dayType + 'DayTypes'] = false;
        }.bind(this));
    }

    /**
     * Loads all absence periods
     */
    function loadAbsencePeriods () {
      var self = this;

      return AbsencePeriod.all()
        .then(function (periods) {
          self.absencePeriods = periods;
        });
    }

    /**
     * Initializes leave request day types
     *
     * @return {Promise}
     */
    function loadDayTypes () {
      var self = this;

      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (dayTypes) {
          self.requestDayTypes = dayTypes;
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
      if (this.directiveOptions.selectedContactId) {
        // In case of a pre-selected contact administration
        return Contact.find(this.directiveOptions.selectedContactId)
          .then(function (contact) {
            this.managedContacts = [contact];
          }.bind(this));
      } else if (this.isRole('admin')) {
        // In case of general administration
        return Contact.all()
          .then(function (contacts) {
            this.managedContacts = _.remove(contacts.list, function (contact) {
              // Removes the admin from the list of contacts
              return contact.id !== loggedInContactId;
            });
          }.bind(this));
      } else {
        // In any other case (including managing)
        return Contact.find(loggedInContactId)
          .then(function (contact) {
            return contact.leaveManagees();
          })
          .then(function (contacts) {
            this.managedContacts = contacts;
          }.bind(this));
      }
    }

    /**
     * Initializes leave request statuses
     *
     * @return {Promise}
     */
    function loadStatuses () {
      var self = this;

      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          self.requestStatuses = _.indexBy(statuses, 'name');
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
     * Dismiss modal on successful creation on submit
     */
    function ok () {
      // @TODO handle closure to pass data back to callee
      this.$modalInstance.close({
        $value: this.request
      });
    }

    /**
     * It filters the breakdown to obtain the ones for currently selected page.
     */
    function pageChanged () {
      var begin = (this.currentPage - 1) * this.numPerPage;
      var end = begin + this.numPerPage;

      this.filteredbreakdown = vm.balance.change.breakdown.slice(begin, end);
    }

    /**
     * Called after successful submission of leave request
     *
     * @param {String} eventName name of the event to emit
     */
    function postSubmit (eventName) {
      broadcastRequestUpdatedEvent.call(this);
      $rootScope.$emit(eventName, this.request);

      this.errors = [];
      // close the modal
      this.ok();
    }

    /**
     * Helper function to reset pagination for balance breakdow
     */
    function rePaginate () {
      this.pagination.totalItems = this.balance.change.breakdown.length;
      this.pagination.filteredbreakdown = this.balance.change.breakdown;
      this.pagination.pageChanged();
    }

    /**
     * Sets entitlements and sets the absences type available for the user.
     * It depends on absenceTypesAndIds to be set to list of absence types and ids
     *
     * @param {Object} absenceTypesAndIds contains all absencetypes and their ids
     * @return {Promise}
     */
    function setAbsenceTypesFromEntitlements (absenceTypesAndIds) {
      var self = this;

      return Entitlement.all({
        contact_id: self.request.contact_id,
        period_id: self.period.id,
        type_id: { IN: absenceTypesAndIds.ids }
      }, true) // `true` because we want to use the 'future' balance for calculation
        .then(function (entitlements) {
          // create a list of absence types with a `balance` property
          self.absenceTypes = mapAbsenceTypesWithBalance(absenceTypesAndIds.types, entitlements);
          if (!self.absenceTypes.length) {
            return $q.reject(NO_ENTITLEMENT_ERROR);
          }
        });
    }

    /**
     * Sets day selection mode: multiple days or a single day
     */
    function setDaySelectionMode () {
      if ((this.isMode('edit') && this.request.from_date === this.request.to_date) ||
        (this.isMode('create') && this.isLeaveType('sickness'))) {
        this.uiOptions.multipleDays = false;
      }
    }

    /**
     * Sets the collection for given day types to sent list of day types,
     * also initializes the day types
     *
     * @param {String} dayType like `from` or `to`
     * @param {Array} listOfDayTypes collection of available day types
     */
    function setDayType (dayType, listOfDayTypes) {
      // will create either of leaveRequestFromDayTypes or leaveRequestToDayTypes key
      var keyForDayTypeCollection = 'request' + _.startCase(dayType) + 'DayTypes';

      this[keyForDayTypeCollection] = listOfDayTypes;

      if (this.isMode('create')) {
        this.request[dayType + '_date_type'] = this[keyForDayTypeCollection][0].value;
      }
    }

    /**
     * Set initial values to absence types when opening the popup
     */
    function setInitialAbsenceTypes () {
      if (this.isMode('create')) {
        // Assign the first absence type to the leave request
        this.selectedAbsenceType = this.absenceTypes[0];
        this.request.type_id = this.selectedAbsenceType.id;
      } else {
        // Either View or Edit Mode
        this.selectedAbsenceType = getSelectedAbsenceType.call(this);
      }
    }

    /**
     * Set Initial attribute
     */
    function setInitialAttributes () {
      initialLeaveRequestAttributes = angular.copy(this.request.attributes());
    }

    /**
     * Submits the form, only if the leave request is valid, also emits event
     * to notify event subscribers about the the save.
     * Updates request based on role and mode
     */
    function submit () {
      var originalStatus = this.request.status_id;

      if (this.isMode('view') || this.submitting) {
        return;
      }

      this.submitting = true;
      changeStatusBeforeSave.call(this);

      return validateBeforeSubmit.call(this)
        .then(function () {
          return this.isMode('edit') ? updateRequest.call(this) : createRequest.call(this);
        }.bind(this))
        .catch(function (errors) {
          // if there is an error, put back the original status
          this.request.status_id = originalStatus;
          errors && handleError.call(this, errors);
        }.bind(this))
        .finally(function () {
          this.submitting = false;
        }.bind(this));
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
     * This should be called whenever a date has been changed
     * First it syncs `from` and `to` date, if it's in 'single day' mode
     * Then, if all the dates are there, it gets the balance change
     *
     * @param {Date} date - the selected date
     * @param {String} dayType - set to from if from date is selected else to
     * @return {Promise}
     */
    function updateAbsencePeriodDatesTypes (date, dayType) {
      return this.loadAbsencePeriodDatesTypes(date, dayType)
      .then(function () {
        return this.updateBalance();
      }.bind(this))
      .catch(function (errors) {
        handleError.call(this, errors);
        this._setDateAndTypes();
      }.bind(this));
    }

    /**
     * Whenever the absence type changes, update the balance opening.
     * Also the balance change needs to be recalculated, if the `from` and `to`
     * dates have been already selected
     */
    function updateBalance () {
      this.selectedAbsenceType = getSelectedAbsenceType.call(this);
      // get the `balance` of the newly selected absence type
      this.balance.opening = this.selectedAbsenceType.remainder;

      this.calculateBalanceChange();
    }

    /**
     * Validates and updates the leave request
     *
     * @returns {Promise}
     */
    function updateRequest () {
      return this.request.update()
        .then(triggerChildComponentsSubmitAndWaitForResponse)
        .then(function () {
          if (this.isRole('manager')) {
            postSubmit.call(this, 'LeaveRequest::updatedByManager');
          } else if (this.isRole('staff') || this.isRole('admin')) {
            postSubmit.call(this, 'LeaveRequest::edit');
          }
        }.bind(this));
    }

    /**
     * Validates a Leave request before submitting
     *
     * @returns {Promise}
     */
    function validateBeforeSubmit () {
      if (this.balance.closing < 0 && this.selectedAbsenceType.allow_overuse === '0') {
        // show an error
        return $q.reject(['You cannot make a request for this leave type at this time ' +
          'as this would leave you with a negative balance']);
      }

      return this.request.isValid();
    }

    /**
     * Calculates and updates opening and closing balances
     */
    function _calculateOpeningAndClosingBalance () {
      this.balance.opening = this.selectedAbsenceType.remainder;
      // the change is negative so adding it will actually subtract it
      this.balance.closing = this.balance.opening + this.balance.change.amount;
    }

    /**
     * Finds if date is in any absence period and sets absence period for the given date
     *
     * @param {Date/String} date
     * @return {Promise} with true value if period found else rejected false
     */
    function _checkAndSetAbsencePeriod (date) {
      var formattedDate = moment(date).format(this.uiOptions.userDateFormat.toUpperCase());

      this.period = _.find(this.absencePeriods, function (period) {
        return period.isInPeriod(formattedDate);
      });

      if (!this.period) {
        this.period = {};
        // inform user if absence period is not found
        this.loading['fromDayTypes'] = false;
        return $q.reject('Please change date as it is not in any absence period');
      }

      return $q.resolve(true);
    }

    /**
     * Converts given date to javascript date as expected by uib-datepicker
     *
     * @param {String} date from server
     * @return {Date}
     */
    function _convertDateFormatFromServer (date) {
      return moment(date, sharedSettings.serverDateFormat).toDate();
    }

    /**
     * Converts given date to server format
     *
     * @param {Date} date
     * @return {String} date converted to server format
     */
    function _convertDateToServerFormat (date) {
      return moment(date).format(sharedSettings.serverDateFormat);
    }

    /**
     * Initializes the controller on loading the dialog
     *
     * @return {Promise}
     */
    function _init () {
      initAvailableStatusesMatrix.call(this);
      initListeners.call(this);

      return loadLoggedInContactId.call(this)
        .then(initRoles.bind(this))
        .then(this._initRequest.bind(this))
        .then(loadStatuses.bind(this))
        .then(function () {
          initOpenMode.call(this);

          return this.canManage && !this.isMode('edit') && loadManagees.call(this);
        }.bind(this))
        .then(loadAbsencePeriods.bind(this))
        .then(function () {
          initAbsencePeriod.call(this);
          this._setMinMaxDate();
        }.bind(this))
        .then(function () {
          if (this.directiveOptions.selectedContactId) {
            this.request.contact_id = this.directiveOptions.selectedContactId;
          }

          setDaySelectionMode.call(this);

          // The additional check here prevents error being displayed on startup when no contact is selected
          if (this.request.contact_id) {
            return this.initAfterContactSelection();
          }
        }.bind(this))
        .catch(handleError.bind(this));
    }

    /**
     * Initialize request attributes based on directive
     *
     * @return {Object} attributes
     */
    function _initRequestAttributes () {
      var attributes = {};

      // if set indicates self leaverequest is either being managed or edited
      if (this.directiveOptions.leaveRequest) {
        // _.deepClone or angular.copy were not uploading files correctly
        attributes = this.directiveOptions.leaveRequest.attributes();
      } else if (!this.canManage) {
        attributes = { contact_id: loggedInContactId };
      }

      return attributes;
    }

    /**
     * Initializes values for absence types and entitlements when the
     * leave request popup model is displayed
     *
     * @return {Promise}
     */
    function _loadAbsenceTypes () {
      var self = this;

      return AbsenceType.all(self.initParams.absenceType)
        .then(function (absenceTypes) {
          var absenceTypesIds = absenceTypes.map(function (absenceType) {
            return absenceType.id;
          });

          absenceTypesAndIds = {
            types: absenceTypes,
            ids: absenceTypesIds
          };

          return setAbsenceTypesFromEntitlements.call(self, absenceTypesAndIds);
        });
    }

    /**
     * Initializes user's calendar (work patterns)
     *
     * @return {Promise}
     */
    function _loadCalendar () {
      var self = this;

      return Calendar.get(self.request.contact_id, self.period.start_date, self.period.end_date)
        .then(function (usersCalendar) {
          self.calendar = usersCalendar;
        });
    }

    /**
     * Resets data in dates, types, balance.
     */
    function _reset () {
      this.uiOptions.toDate = this.uiOptions.fromDate;
      this.request.to_date_type = this.request.from_date_type;
      this.request.to_date = this.request.from_date;

      this.calculateBalanceChange();
    }

    /**
     * Sets dates and types for this.request from UI
     */
    function _setDateAndTypes () {
      this._setDates();

      if (this.uiOptions.multipleDays) {
        this.uiOptions.showBalance = !!this.request.from_date && !!this.request.from_date_type &&
          !!this.request.to_date && !!this.request.to_date_type && !!this.period.id;
      } else {
        if (this.uiOptions.fromDate) {
          this.request.to_date_type = this.request.from_date_type;
        }

        this.uiOptions.showBalance = !!this.request.from_date && !!this.request.from_date_type && !!this.period.id;
      }
    }

    /**
     * Sets dates and types for this.request from UI
     */
    function _setDates () {
      this.request.from_date = this.uiOptions.fromDate ? this._convertDateToServerFormat(this.uiOptions.fromDate) : null;
      this.request.to_date = this.uiOptions.toDate ? this._convertDateToServerFormat(this.uiOptions.toDate) : null;

      if (!this.uiOptions.multipleDays && this.uiOptions.fromDate) {
        this.uiOptions.toDate = this.uiOptions.fromDate;
        this.request.to_date = this.request.from_date;
      }
    }

    /**
     * Sets the min and max for to date from absence period. It also sets the
     * init/starting date which user can select from. For multiple days request
     * user can select to date which is one more than the the start date.
     */
    function _setMinMaxDate () {
      if (this.uiOptions.fromDate) {
        var nextFromDay = moment(this.uiOptions.fromDate).add(1, 'd').toDate();

        this.uiOptions.date.to.options.minDate = nextFromDay;
        this.uiOptions.date.to.options.initDate = nextFromDay;

        // also re-set to date if from date is changing and less than to date
        if (this.uiOptions.toDate && moment(this.uiOptions.toDate).isBefore(this.uiOptions.fromDate)) {
          this.uiOptions.toDate = this.uiOptions.fromDate;
        }
      } else {
        this.uiOptions.date.to.options.minDate = this._convertDateFormatFromServer(this.period.start_date);
        this.uiOptions.date.to.options.initDate = this.uiOptions.date.to.options.minDate;
      }

      this.uiOptions.date.to.options.maxDate = this._convertDateFormatFromServer(this.period.end_date);
    }
  }
});
