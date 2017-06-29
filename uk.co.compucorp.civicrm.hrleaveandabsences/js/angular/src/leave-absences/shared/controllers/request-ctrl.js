/* eslint-env amd */
define([
  'common/angular',
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'common/services/api/option-group',
  'common/services/hr-settings',
  'common/models/contact',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/public-holiday-model'
], function (angular, controllers, _, moment) {
  'use strict';

  controllers.controller('RequestCtrl', [
    '$log', '$q', '$rootScope', 'Contact', 'dialog', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'PublicHoliday', 'shared-settings',
    function ($log, $q, $rootScope, Contact, dialog, AbsencePeriod, AbsenceType,
      OptionGroup, Calendar, Entitlement, HRSettings,
      LeaveRequest, PublicHoliday, sharedSettings
    ) {
      $log.debug('RequestCtrl');
      var absenceTypesAndIds;
      var initialLeaveRequestAttributes = {}; // used to compare the change in leaverequest in edit mode
      var mode = ''; // can be edit, create, view
      var role = '';
      var NO_ENTITLEMENT_ERROR = 'No entitlement';

      this.absencePeriods = [];
      this.absenceTypes = [];
      this.calendar = {};
      this.contactName = null;
      this.errors = [];
      this.managedContacts = [];
      this.newStatusOnSave = null;
      this.period = {};
      this.postContactSelection = false; // flag to track if user is selected for enabling UI
      this.requestDayTypes = [];
      this.requestStatuses = {};
      this.selectedAbsenceType = {};
      this.statusNames = sharedSettings.statusNames;
      this.submitting = false;
      this.supportedFileTypes = '';
      this.today = Date.now();
      this.balance = {
        closing: 0,
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        }
      };
      this.comment = {
        text: '',
        contacts: {}
      };
      this.loading = {
        absenceTypes: true,
        showBalanceChange: false,
        fromDayTypes: false,
        toDayTypes: false
      };
      // TODO temp fix to allow pageChanged to be called from html as well from functions here with proper context
      var parentThis = this;
      this.pagination = {
        currentPage: 1,
        filteredbreakdown: this.balance.change.breakdown,
        numPerPage: 5,
        totalItems: this.balance.change.breakdown.length,
        /**
         * Called when user changes the page under selection. It filters the
         * breakdown to obtain the ones for currently selected page.
         */
        pageChanged: function () {
          // filter breakdowns
          var begin = (this.currentPage - 1) * this.numPerPage;
          var end = begin + this.numPerPage;

          this.filteredbreakdown = parentThis.balance.change.breakdown.slice(begin, end);
        }
      };
      this.uiOptions = {
        isChangeExpanded: false,
        multipleDays: true,
        userDateFormat: HRSettings.DATE_FORMAT,
        userDateFormatWithTime: HRSettings.DATE_FORMAT + ' HH:mm',
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

      /**
       * Add a comment into comments array, also clears the comments textbox
       */
      this.addComment = function () {
        this.request.comments.push({
          contact_id: this.directiveOptions.contactId,
          created_at: moment(new Date()).format(sharedSettings.serverDateTimeFormat),
          leave_request_id: this.request.id,
          text: this.comment.text
        });
        this.comment.text = '';
      };

      /**
       * Change handler when changing no. of days like Multiple Days or Single Day.
       * It will reset dates, day types, change balance.
       */
      this.changeInNoOfDays = function () {
        this._reset();
        this._calculateOpeningAndClosingBalance();
      };

      /**
       * When user cancels the model dialog
       */
      this.cancel = function () {
        this.$modalInstance.dismiss({
          $value: 'cancel'
        });
      };

      /**
       * Calculate change in balance, it updates local balance variables.
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      this.calculateBalanceChange = function () {
        var self = this;

        self._setDateAndTypes();

        if (!canCalculateChange.call(self)) {
          return $q.resolve();
        }

        self.errors = [];
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
      };

      /**
       * Checks if submit button can be enabled for user and returns true if succeeds
       *
       * @return {Boolean}
       */
      this.canSubmit = function () {
        var canSubmit = canCalculateChange.call(this);

        // check if user has changed any attribute
        if (this.isMode('edit')) {
          canSubmit = canSubmit && hasRequestChanged.call(this);
        }

        // check if manager has changed status
        if (this.isRole('manager') && this.requestStatuses) {
          // awaiting_approval will not be available in this.requestStatuses if manager has changed selection
          canSubmit = canSubmit && !!this.getStatusFromValue(this.newStatusOnSave);
        }

        return canSubmit && !this.isMode('view');
      };

      /**
       * Checks if user can upload more file, it totals the number of already
       * uploaded files and those which are in queue and compares it to limit.
       *
       * @return {Boolean} true is user can upload more else false
       */
      this.canUploadMore = function () {
        return this.getFilesCount() < sharedSettings.fileUploader.queueLimit;
      };

      /**
      * Closes the error alerts if any
      */
      this.closeAlert = function () {
        this.errors = [];
      };

      /**
       * Deletes the leave request
       */
      this.deleteLeaveRequest = function () {
        dialog.open({
          title: 'Confirm Deletion?',
          copyCancel: 'Cancel',
          copyConfirm: 'Confirm',
          classConfirm: 'btn-danger',
          msg: 'This cannot be undone',
          onConfirm: function () {
            this.directiveOptions.leaveRequest.delete()
              .then(function () {
                this.cancel();
                $rootScope.$emit('LeaveRequest::deleted');
              }.bind(this));
          }.bind(this)
        });
      };

      /**
       * Calculates the total number of files associated with request.
       *
       * @return {Number} of files
       */
      this.getFilesCount = function () {
        var filesWithSoftDelete = _.filter(this.request.files, function (file) {
          return file.toBeDeleted;
        });

        return this.request.files.length + this.request.fileUploader.queue.length - filesWithSoftDelete.length;
      };

      /**
       * Format a date-time into user format and returns
       *
       * @return {String}
       */
      this.formatDateTime = function (dateTime) {
        return moment(dateTime, sharedSettings.serverDateTimeFormat).format(this.uiOptions.userDateFormat.toUpperCase() + ' HH:mm');
      };

      /**
       * Returns the comment author name
       * @param {String} contact_id
       *
       * @return {String}
       */
      this.getCommentorName = function (contactId) {
        if (contactId === this.directiveOptions.contactId) {
          return 'Me';
        } else if (this.comment.contacts[contactId]) {
          return this.comment.contacts[contactId].display_name;
        }
      };

      /**
       * Returns the comments which are not marked for deletion
       *
       * @return {Array}
       */
      this.getActiveComments = function () {
        return this.request.comments.filter(function (comment) {
          return !comment.toBeDeleted;
        });
      };

      /**
       * Flattens statuses from object to array of objects. This is used to
       * populate the dropdown with array of statuses.
       * Also it checks if given status is available to manager. If manager applies leave
       * on behalf of staff then cancelled is also removed from her list of available statuses.
       *
       * @return {Array}
       */
      this.getStatuses = function () {
        return _.reject(this.requestStatuses, function (status) {
          var canRemoveStatus = (status.name === sharedSettings.statusNames.adminApproved || status.name === sharedSettings.statusNames.awaitingApproval);

          return this.isRole('manager') ? (canRemoveStatus || status.name === 'cancelled') : canRemoveStatus;
        }.bind(this));
      };

      /**
       * Gets status object for given status value
       *
       * @param {String} value - value of the status
       * @return {Object} option group of type status or undefined if not found
       */
      this.getStatusFromValue = function (value) {
        return _.find(this.requestStatuses, function (status) {
          return status.value === value;
        });
      };

      /**
       * Initializes after contact is selected either directly or by manager
       *
       * @return {Promise}
       */
      this.initAfterContactSelection = function () {
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
              initialLeaveRequestAttributes = angular.copy(self.request.attributes());

              if (self.request.from_date === self.request.to_date) {
                self.uiOptions.multipleDays = false;
              }
            }

            self.postContactSelection = false;
            return self.calculateBalanceChange();
          })
          .catch(function (error) {
            if (error !== NO_ENTITLEMENT_ERROR) {
              return $q.reject(error);
            }
          });
      };

      /**
       * Checks if the leave request has the given status
       *
       * @param {String} leaveStatus
       * @return {Boolean}
       */
      this.isLeaveStatus = function (leaveStatus) {
        var status = this.getStatusFromValue(this.request.status_id);

        return status ? status.name === leaveStatus : false;
      };

      /**
       * Checks if popup is opened in given leave type like `leave` or `sickness` or 'toil'
       *
       * @param {String} leaveTypeParam to check the leave type of current request
       * @return {Boolean}
       */
      this.isLeaveType = function (leaveTypeParam) {
        return this.request.request_type === leaveTypeParam;
      };

      /**
       * Checks if popup is opened in given mode
       *
       * @param {String} modeParam to open leave request like edit or view or create
       * @return {Boolean}
       */
      this.isMode = function (modeParam) {
        return mode === modeParam;
      };

      /**
       * Checks if popup is opened in given role
       *
       * @param {String} roleParam like manager, staff
       * @return {Boolean}
       */
      this.isRole = function (roleParam) {
        return role === roleParam;
      };

      /**
       * Dismiss modal on successful creation on submit
       */
      this.ok = function () {
        // todo handle closure to pass data back to callee
        this.$modalInstance.close({
          $value: this.request
        });
      };

      /**
       * Orders comment, used as a angular filter
       * @param {Object} comment
       *
       * @return {Date}
       */
      this.orderComment = function (comment) {
        return moment(comment.created_at, sharedSettings.serverDateTimeFormat);
      };

      /**
       * Decides visiblity of remove comment button
       * @param {Object} comment - comment object
       *
       * @return {Boolean}
       */
      this.removeCommentVisibility = function (comment) {
        return !comment.comment_id || this.isRole('manager');
      };

      /**
       * Decides visiblity of remove attachment button
       * @param {Object} attachment - attachment object
       *
       * @return {Boolean}
       */
      this.removeAttachmentVisibility = function (attachment) {
        return !attachment.attachment_id || this.isRole('manager');
      };

      /**
       * Submits the form, only if the leave request is valid, also emits event
       * to notify event subscribers about the the save.
       * Updates request based on role and mode
       */
      this.submit = function () {
        var originalStatus = this.request.status_id;

        if (this.isMode('view') || this.submitting) {
          return;
        }

        this.submitting = true;
        changeStatusBeforeSave.call(this);

        validateBeforeSubmit.call(this)
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
      };

      /**
       * This should be called whenever a date has been changed
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @param {String} dayType - set to from if from date is selected else to
       * @return {Promise}
       */
      this.updateAbsencePeriodDatesTypes = function (date, dayType) {
        var self = this;
        var oldPeriodId = self.period.id;
        dayType = dayType || 'from';
        self.loading[dayType + 'DayTypes'] = true;

        return self._checkAndSetAbsencePeriod(date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId === self.period.id;

            if (!isInCurrentPeriod) {
              // partial reset is required when user has selected a to date and
              // then changes absence period from from date
              // no reset required for single days and to date changes
              if (self.uiOptions.multipleDays && dayType === 'from') {
                self.uiOptions.showBalance = false;
                self.uiOptions.toDate = null;
                self.request.to_date = null;
                self.request.to_date_type = null;
              }

              return $q.all([
                self._loadAbsenceTypes(),
                self._loadCalendar()
              ]);
            }
          })
          .then(function () {
            self._setMinMaxDate();

            return filterLeaveRequestDayTypes.call(self, date, dayType);
          })
          .then(function () {
            self.loading[dayType + 'DayTypes'] = false;

            return self.updateBalance();
          })
          .catch(function (error) {
            self.errors = [error];
          });
      };

      /**
       * Whenever the absence type changes, update the balance opening.
       * Also the balance change needs to be recalculated, if the `from` and `to`
       * dates have been already selected
       */
      this.updateBalance = function () {
        this.selectedAbsenceType = getSelectedAbsenceType.call(this);
        // get the `balance` of the newly selected absence type
        this.balance.opening = this.selectedAbsenceType.remainder;

        this.calculateBalanceChange();
      };

      /**
       * Calculates and updates opening and closing balances
       */
      this._calculateOpeningAndClosingBalance = function () {
        this.balance.opening = this.selectedAbsenceType.remainder;
        // the change is negative so adding it will actually subtract it
        this.balance.closing = this.balance.opening + this.balance.change.amount;
      };

      /**
       * Finds if date is in any absence period and sets absence period for the given date
       *
       * @param {Date/String} date
       * @return {Promise} with true value if period found else rejected false
       */
      this._checkAndSetAbsencePeriod = function (date) {
        var formattedDate = moment(date).format(this.uiOptions.userDateFormat.toUpperCase());

        this.period = _.find(this.absencePeriods, function (period) {
          return period.isInPeriod(formattedDate);
        });

        if (!this.period) {
          // inform user if absence period is not found
          return $q.reject('Please change date as it is not in any absence period');
        }

        return $q.resolve(true);
      };

      /**
       * Converts given date to server format
       *
       * @param {Date} date
       * @return {String} date converted to server format
       */
      this._convertDateToServerFormat = function (date) {
        return moment(date).format(sharedSettings.serverDateFormat);
      };

      /**
       * Converts given date to javascript date as expected by uib-datepicker
       *
       * @param {String} date from server
       * @return {Date}
       */
      this._convertDateFormatFromServer = function (date) {
        return moment(date, sharedSettings.serverDateFormat).toDate();
      };

      /**
       * Initializes the controller on loading the dialog
       *
       * @return {Promise}
       */
      this._init = function () {
        var self = this;

        this.supportedFileTypes = _.keys(sharedSettings.fileUploader.allowedMimeTypes);
        role = this.directiveOptions.userRole || 'staff';
        this._initRequest();

        return loadStatuses.call(self)
          .then(function () {
            initOpenMode.call(self);

            return self.isRole('manager') && loadManagees.call(self);
          })
          .then(function () {
            return loadAbsencePeriods.call(self);
          })
          .then(function () {
            initAbsencePeriod.call(self);
            self._setMinMaxDate();

            return $q.all([
              loadCommentsAndContactNames.call(self),
              self.request.loadAttachments()
            ]);
          })
          .then(function () {
            // The additional check here prevents error being displayed on startup when no contact is selected
            if (self.request.contact_id) {
              return self.initAfterContactSelection();
            }
          })
          .catch(handleError.bind(self));
      };

      /**
       * Initialize request attributes based on directive
       *
       * @return {Object} attributes
       */
      this._initRequestAttributes = function () {
        var attributes = {};

        // if set indicates self leaverequest is either being managed or edited
        if (this.directiveOptions.leaveRequest) {
          // _.deepClone or angular.copy were not uploading files correctly
          attributes = this.directiveOptions.leaveRequest.attributes();
        } else if (!this.isRole('manager')) {
          attributes = { contact_id: this.directiveOptions.contactId };
        }

        return attributes;
      };

      /**
       * Initializes user's calendar (work patterns)
       *
       * @return {Promise}
       */
      this._loadCalendar = function () {
        var self = this;

        return Calendar.get(self.request.contact_id, self.period.id)
          .then(function (usersCalendar) {
            self.calendar = usersCalendar;
          });
      };

      /**
       * Initializes values for absence types and entitlements when the
       * leave request popup model is displayed
       *
       * @return {Promise}
       */
      this._loadAbsenceTypes = function () {
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
      };

      /**
       * Resets data in dates, types, balance.
       */
      this._reset = function () {
        this.uiOptions.toDate = this.uiOptions.fromDate;
        this.request.to_date_type = this.request.from_date_type;
        this.request.to_date = this.request.from_date;

        this.calculateBalanceChange();
      };

      /**
       * Sets dates and types for this.request from UI
       */
      this._setDates = function () {
        this.request.from_date = this.uiOptions.fromDate ? this._convertDateToServerFormat(this.uiOptions.fromDate) : null;
        this.request.to_date = this.uiOptions.toDate ? this._convertDateToServerFormat(this.uiOptions.toDate) : null;

        if (!this.uiOptions.multipleDays && this.uiOptions.fromDate) {
          this.uiOptions.toDate = this.uiOptions.fromDate;
          this.request.to_date = this.request.from_date;
        }
      };

      /**
       * Sets dates and types for this.request from UI
       */
      this._setDateAndTypes = function () {
        this._setDates();

        if (this.uiOptions.multipleDays) {
          this.uiOptions.showBalance = !!this.request.to_date && !!this.request.from_date;
        } else {
          if (this.uiOptions.fromDate) {
            this.request.to_date_type = this.request.from_date_type;
          }

          this.uiOptions.showBalance = !!this.request.from_date;
        }
      };

      /**
       * Sets the min and max for to date from absence period. It also sets the
       * init/starting date which user can select from. For multiple days request
       * user can select to date which is one more than the the start date.
       */
      this._setMinMaxDate = function () {
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
      };

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
       * Changes status of the leave request before saving it
       * For staff the status_id should be always set to awaitingApproval before saving
       * If manager has changed the status through dropdown, assign the same before calling API
       */
      function changeStatusBeforeSave () {
        if (this.isRole('staff')) {
          this.request.status_id = this.requestStatuses[sharedSettings.statusNames.awaitingApproval].value;
        } else if (this.isRole('manager')) {
          this.request.status_id = this.newStatusOnSave || this.request.status_id;
        }
      }

      /**
       * Validates and creates the leave request
       *
       * @returns {Promise}
       */
      function createRequest () {
        return this.request.create()
          .then(function () {
            postSubmit.call(this, 'LeaveRequest::new');
          }.bind(this));
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
       * @return {Promise} of array with day types
       */
      function filterLeaveRequestDayTypes (date, dayType) {
        var inCalendarList, listToReturn;
        var deferred = $q.defer();

        if (!date) {
          deferred.reject([]);
        }

        // Make a copy of the list
        listToReturn = this.requestDayTypes.slice(0);

        date = this._convertDateToServerFormat(date);
        PublicHoliday.isPublicHoliday(date)
          .then(function (result) {
            if (result) {
              listToReturn = listToReturn.filter(function (publicHoliday) {
                return publicHoliday.name === 'public_holiday';
              });
            } else {
              inCalendarList = getDayTypesFromDate.call(this, date, listToReturn);

              if (!inCalendarList.length) {
                // 'All day', 'Half-day AM', and 'Half-day PM' options
                listToReturn = listToReturn.filter(function (dayType) {
                  return dayType.name === 'all_day' || dayType.name === 'half_day_am' || dayType.name === 'half_day_pm';
                });
              } else {
                listToReturn = inCalendarList;
              }
            }

            setDayType.call(this, dayType, listToReturn);
            deferred.resolve(listToReturn);
          }.bind(this));

        return deferred.promise;
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
       * Gets list of day types if its found to be weekend or non working in calendar
       *
       * @param {Date} date to Checks
       * @param {Array} listOfDayTypes array of day types
       * @return {Array} non-empty if found else empty array
       */
      function getDayTypesFromDate (date, listOfDayTypes) {
        var nameFilter = null;

        if (this.calendar.isNonWorkingDay(moment(date))) {
          nameFilter = 'non_working_day';
        } else if (this.calendar.isWeekend(moment(date))) {
          nameFilter = 'weekend';
        }

        return !nameFilter ? [] : listOfDayTypes.filter(function (day) {
          return day.name === nameFilter;
        });
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

      function handleError (errors) {
        // show errors
        this.errors = _.isArray(errors) ? errors : [errors];

        // reset loading Checks
        this.loading.showBalanceChange = false;
        this.loading.absenceTypes = false;
        this.loading.fromDayTypes = false;
        this.loading.toDayTypes = false;

        this.submitting = false;
      }

      /**
       * Checks if a leave request has been changed since opening the modal
       *
       * FileUploader property deleted because it will not be used
       * in object comparison
       *
       * @return {Boolean}
       */
      function hasRequestChanged () {
        // using angular.equals to automatically ignore the $$hashkey property
        return !angular.equals(
          _.omit(initialLeaveRequestAttributes, 'fileUploader'),
          _.omit(this.request.attributes(), 'fileUploader')
        ) || this.request.fileUploader.queue.length !== 0 ||
          (this.isRole('manager') && this.newStatusOnSave);
      }

      /**
       * Initialize open mode of the dialog
       */
      function initOpenMode () {
        if (this.request.id) {
          mode = 'edit';

          var viewModeStatuses = [
            this.requestStatuses[sharedSettings.statusNames.approved].value,
            this.requestStatuses[sharedSettings.statusNames.adminApproved].value,
            this.requestStatuses[sharedSettings.statusNames.rejected].value,
            this.requestStatuses[sharedSettings.statusNames.cancelled].value
          ];

          if (this.isRole('staff') && viewModeStatuses.indexOf(this.request.status_id) > -1) {
            mode = 'view';
          }
        } else {
          mode = 'create';
        }
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
       * Initialize from and to dates and day types.
       * It will also set the day types.
       *
       * @return {Promise}
       */
      function initDates () {
        if (!this.isMode('create')) {
          var attributes = this.request.attributes();

          this.uiOptions.fromDate = this._convertDateFormatFromServer(this.request.from_date);

          return this.updateAbsencePeriodDatesTypes(this.uiOptions.fromDate, 'from')
            .then(function () {
              // to_date and type has been reset in above call so reinitialize from clone
              this.request.to_date = attributes.to_date;
              this.request.to_date_type = attributes.to_date_type;
              this.uiOptions.toDate = this._convertDateFormatFromServer(this.request.to_date);
              return this.updateAbsencePeriodDatesTypes(this.uiOptions.toDate, 'to');
            }.bind(this));
        } else {
          return $q.resolve();
        }
      }

      /**
       * Initialize status
       */
      function initStatus () {
        if (this.isMode('create') && this.isRole('manager')) {
          this.newStatusOnSave = this.requestStatuses[sharedSettings.statusNames.approved].value;
        }
      }

      /**
       * Initialize contact
       *
       * {Promise}
       */
      function initContact () {
        if (this.isRole('manager')) {
          return Contact.find(this.request.contact_id)
            .then(function (contact) {
              this.contactName = contact.display_name;
            }.bind(this));
        }

        return $q.resolve();
      }

      /**
       * Loads the managees of currently logged in user
       *
       * @return {Promise}
       */
      function loadManagees () {
        return Contact.find(this.directiveOptions.contactId)
          .then(function (contact) {
            return contact.leaveManagees();
          })
          .then(function (contacts) {
            this.managedContacts = contacts;
          }.bind(this));
      }

      /**
       * Loads the comments for current leave request
       *
       * @return {Promise}
       */
      function loadCommentsAndContactNames () {
        return this.request.loadComments()
          .then(function () {
            // loadComments sets the comments on request object instead of returning it
            this.request.comments.length && loadContactNames.call(this);
          }.bind(this));
      }

      /**
       * Loads unique contact names for all the comments
       *
       * @return {Promise}
       */
      function loadContactNames () {
        var contactIDs = [];

        _.each(this.request.comments, function (comment) {
          // Push only unique contactId's which are not same as logged in user
          if (comment.contact_id !== this.directiveOptions.contactId && contactIDs.indexOf(comment.contact_id) === -1) {
            contactIDs.push(comment.contact_id);
          }
        }.bind(this));

        return Contact.all({
          id: { IN: contactIDs }
        }, { page: 1, size: 0 })
        .then(function (contacts) {
          this.comment.contacts = _.indexBy(contacts.list, 'contact_id');
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
       * Called after successful submission of leave request
       *
       * @param {String} eventName name of the event to emit
       */
      function postSubmit (eventName) {
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
       * Validates and updates the leave request
       *
       * @returns {Promise}
       */
      function updateRequest () {
        return this.request.update()
          .then(function () {
            if (this.isRole('manager')) {
              postSubmit.call(this, 'LeaveRequest::updatedByManager');
            } else if (this.isRole('staff')) {
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
          return $q.reject(['You are not allowed to apply leave in negative']);
        }

        return this.request.isValid();
      }

      return this;
    }
  ]);
});
