define([
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
  'leave-absences/shared/models/public-holiday-model',
], function (controllers, _, moment) {
  'use strict';

  controllers.controller('RequestCtrl', [
    '$log', '$q', '$rootScope', 'Contact', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'PublicHoliday', 'shared-settings',
    function ($log, $q, $rootScope, Contact, AbsencePeriod, AbsenceType,
      OptionGroup, Calendar, Entitlement, HR_settings,
      LeaveRequest, PublicHoliday, sharedSettings
    ) {
      $log.debug('RequestCtrl');

      var absenceTypesAndIds,
        initialLeaveRequestAttributes = {}, //used to compare the change in leaverequest in edit mode
        mode = '', //can be edit, create, view
        role = '',
        initialCommentsLength = 0; //number of comments when the request model is loaded

      this.absencePeriods = [];
      this.absenceTypes = [];
      this.calendar = {};
      this.contactName = null;
      this.submitting = false;
      this.errors = [];
      this.managedContacts = [];
      this.requestDayTypes = [];
      this.selectedAbsenceType = {};
      this.period = {};
      this.requestStatuses = {};
      this.statusBeforeEdit = {};
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
        postContactSelection: false,
        toDayTypes: false
      };
      //TODO temp fix to allow pageChanged to be called from html as well from functions here with proper context
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
          //filter breakdowns
          var begin = (this.currentPage - 1) * this.numPerPage,
            end = begin + this.numPerPage;

          this.filteredbreakdown = parentThis.balance.change.breakdown.slice(begin, end);
        }
      };
      this.uiOptions = {
        isChangeExpanded: false,
        multipleDays: true,
        userDateFormat: HR_settings.DATE_FORMAT,
        userDateFormatWithTime: HR_settings.DATE_FORMAT + ' HH:mm',
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
       * Change handler for change request type like multiple or single. It will
       * reset dates, day types, change balance.
       */
      this.changeInNoOfDays = function () {
        this._reset();
        //reinitialize opening balance
        initAbsenceType.call(this);
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
       * Closes the error alerts if any
       */
      this.closeAlert = function () {
        this.errors = [];
      };

      /**
       * Calculate change in balance, it updates local balance variables.
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      this.calculateBalanceChange = function () {
        var self = this;

        self._setDateAndTypes.call(self);

        if (!canCalculateChange.call(self)) {
          return $q.resolve();
        }

        self.errors = [];
        self.loading.showBalanceChange = true;
        return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange.call(self))
          .then(function (balanceChange) {
            if (balanceChange) {
              self.balance.change = balanceChange;
              //the change is negative so adding it will actually subtract it
              self.balance.closing = self.balance.opening + self.balance.change.amount;
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

        //check if user has changed any attribute
        if (this.isMode('edit')) {
          canSubmit = canSubmit && !_.isEqual(initialLeaveRequestAttributes, this.request.attributes());
          //user has added a comment
          canSubmit = canSubmit || this.request.comments.length !== initialCommentsLength;
        }

        //check if manager has changed status
        if (this.isRole('manager') && this.requestStatuses) {
          //waiting_approval will not be available in this.requestStatuses if manager has changed selection
          canSubmit = canSubmit && !!getStatusFromValue.call(this, this.request.status_id);
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
        return (this.request.files.length + this.request.fileUploader.queue.length) < sharedSettings.fileUploader.queueLimit;
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
      this.getCommentorName = function (contact_id) {
        if (contact_id == this.directiveOptions.contactId) {
          return 'Me';
        } else if (this.comment.contacts[contact_id]) {
          return this.comment.contacts[contact_id].display_name;
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
        //todo handle closure to pass data back to callee
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
       * to listeners self leaverequest is created.
       * Also, checks if its an update request from manager and accordingly updates leave request
       */
      this.submit = function () {
        if (this.isMode('view') || this.submitting) {
          return;
        }

        // current absence type (this.request.type_id) doesn't allow self
        if (this.balance.closing < 0 && this.selectedAbsenceType.allow_overuse == '0') {
          // show an error
          this.errors = ['You are not allowed to apply leave in negative'];
          return;
        }

        this.errors = [];

        if (canViewOrEdit.call(this)) {
          updateRequest.call(this);
        } else {
          createRequest.call(this);
        }
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
        var self = this,
          oldPeriodId = self.period.id;
        dayType = dayType || 'from';
        self.loading[dayType + 'DayTypes'] = true;

        return self._checkAndSetAbsencePeriod.call(self, date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId == self.period.id;

            if (!isInCurrentPeriod) {
              //partial reset is required when user has selected a to date and
              //then changes absence period from from date
              //no reset required for single days and to date changes
              if (self.uiOptions.multipleDays && dayType === 'from') {
                self.uiOptions.showBalance = false;
                self.uiOptions.toDate = null;
                self.request.to_date = null;
                self.request.to_date_type = null;
              }

              return $q.all([
                self._loadAbsenceTypes.call(self),
                self._loadCalendar.call(self)
              ]);
            }
          })
          .then(function () {
            self._setMinMaxDate.call(self);

            return filterLeaveRequestDayTypes.call(self, date, dayType);
          })
          .then(function () {
            self.loading[dayType + 'DayTypes'] = false;

            return self.updateBalance.call(self);
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

        this.calculateBalanceChange.call(this);
      };

      /**
       * Initialize request attributes based on directive
       *
       * @return {Object} attributes
       */
      this._initRequestAttributes = function () {
        var attributes = {};

        //if set indicates self leaverequest is either being managed or edited
        if (this.directiveOptions.leaveRequest) {
          //_.deepClone or angular.copy were not uploading files correctly
          attributes = this.directiveOptions.leaveRequest.attributes();
        } else if (!this.isRole('manager')) {
          attributes = { contact_id: this.directiveOptions.contactId };
        }

        return attributes;
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
          //inform user if absence period is not found
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
      }

      /**
       * Converts given date to javascript date as expected by uib-datepicker
       *
       * @param {String} date from server
       * @return {Date}
       */
      this._convertDateFormatFromServer = function (date) {
        return moment(date, sharedSettings.serverDateFormat).toDate();
      }

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
          var canRemoveStatus = (status.name === 'admin_approved' || status.name === 'waiting_approval');

          return this.isRole('manager') ? (canRemoveStatus || status.name === 'cancelled') : canRemoveStatus;
        }.bind(this));
      }

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
        this._setDates.call(this);

        if (this.uiOptions.multipleDays) {
          this.uiOptions.showBalance = !!this.request.to_date && !!this.request.from_date;
        } else {
          if (this.uiOptions.fromDate) {
            this.request.to_date_type = this.request.from_date_type;
          }

          this.uiOptions.showBalance = !!this.request.from_date;
        }
      }

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

          //also re-set to date if from date is changing and less than to date
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
       * Initializes the controller on loading the dialog
       *
       * @return {Promise}
       */
      this._init = function () {
        var self = this;

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
            if (self.request.contact_id) {
              return self.initAfterContactSelection();
            }
          })
          .catch(handleError.bind(self));
      };

      /**
       * Initializes after contact is selected either directly or by manager
       *
       * @return {Promise}
       */
      this.initAfterContactSelection = function () {
        var self = this;
        self.loading.postContactSelection = true;

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
            initAbsenceType.call(self);
            initStatus.call(self);
            initContact.call(self);

            if (self.isMode('edit')) {
              initialLeaveRequestAttributes = self.request.attributes();

              if (self.request.from_date === self.request.to_date) {
                self.uiOptions.multipleDays = false;
              }

              initialCommentsLength = self.request.comments.length;
            }

            self.loading.postContactSelection = false;
          })
          .catch(handleError.bind(self));
      };

      /**
       * Checks if all params are set to calculate balance
       *
       * @param {Boolean} true if all present else false
       */
      function canCalculateChange() {
        return !!this.request.from_date && !!this.request.to_date &&
          !!this.request.from_date_type && !!this.request.to_date_type;
      }

      /**
       * Creates leaverequest
       */
      function createRequest() {
        var self = this;
        self.submitting = true;

        self.request.isValid()
          .then(function () {
            self.request.create()
              .then(function (result) {
                // refresh the list
                postSubmit.call(self, 'LeaveRequest::new');
                self.submitting = false;
              })
              .catch(handleError.bind(self));
          })
          .catch(handleError.bind(self));
      }

      /**
       * Checks if user can view or edit leaverequest
       *
       * @return {Boolean}
       */
      function canViewOrEdit() {
        return this.isMode('edit') || this.isMode('view');
      }

      /**
       * Maps absence types to be more compatible for UI selection
       *
       * @param {Array} absenceTypes
       * @param {Object} entitlements
       * @return {Array} of filtered absence types for given entitlements
       */
      function mapAbsenceTypesWithBalance(absenceTypes, entitlements) {
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
       * This method will be used on the view to return a list of available
       * leave request day types (All day, 1/2 AM, 1/2 PM, Non working day,
       * Weekend, Public holiday) for the given date (which is the date
       * selected by the user via datepicker)
       *
       * If no date is passed, then no list is returned
       *
       * @param  {Date} date
       * @param  {String} dayType - set to from if from date is selected else to
       * @return {Promise} of array with day types
       */
      function filterLeaveRequestDayTypes(date, dayType) {
        var deferred = $q.defer(),
          inCalendarList,
          listToReturn,
          self = this;

        if (!date) {
          deferred.reject([]);
        }

        // Make a copy of the list
        listToReturn = self.requestDayTypes.slice(0);

        date = self._convertDateToServerFormat.call(self, date);
        PublicHoliday.isPublicHoliday(date)
          .then(function (result) {
            if (result) {
              listToReturn = listToReturn.filter(function (publicHoliday) {
                return publicHoliday.name === 'public_holiday';
              });
            } else {
              inCalendarList = getDayTypesFromDate.call(self, date, listToReturn);

              if (!inCalendarList.length) {
                // 'All day', '1/2 AM', and '1/2 PM' options
                listToReturn = listToReturn.filter(function (dayType) {
                  return dayType.name === 'all_day' || dayType.name === 'half_day_am' || dayType.name === 'half_day_pm';
                });
              } else {
                listToReturn = inCalendarList;
              }
            }

            setDayType.call(self, dayType, listToReturn);
            deferred.resolve(listToReturn);
          });

        return deferred.promise;
      }

      /**
       * Helper function to obtain params for leave request calculateBalanceChange api call
       *
       * @return {Object} containing required keys for leave request
       */
      function getParamsForBalanceChange() {
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
      function getDayTypesFromDate(date, listOfDayTypes) {
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
      function getSelectedAbsenceType() {
        var self = this;

        return _.find(self.absenceTypes, function (absenceType) {
          return absenceType.id == self.request.type_id;
        });
      }

      /**
       * Gets status object for given status value
       *
       * @param value of the status
       * @return {Object} option group of type status or undefined if not found
       */
      function getStatusFromValue(value) {
        return _.find(this.requestStatuses, function (status) {
          return status.value == value;
        });
      }

      /**
       * Error handler, generally used in catch calls
       */
      function handleError(errors) {
        // show errors
        this.errors = _.isArray(errors) ? errors : [errors];

        //reset loading Checks
        this.loading.showBalanceChange = false;
        this.loading.absenceTypes = false;
        this.loading.fromDayTypes = false;
        this.loading.toDayTypes = false;

        this.submitting = false;
      }

      /**
       * Initialize open mode of the dialog
       */
      function initOpenMode() {
        if (this.request.id) {
          mode = 'edit';

          var viewModes = [this.requestStatuses['approved'].value, this.requestStatuses['admin_approved'].value,
            this.requestStatuses['rejected'].value, this.requestStatuses['cancelled'].value
          ];

          if (this.isRole('staff') && viewModes.indexOf(this.request.status_id) > -1) {
            mode = 'view';
          }
        } else {
          mode = 'create';
        }
      }

      /**
       * Inits absence period for the current date
       */
      function initAbsencePeriod() {
        this.period = _.find(this.absencePeriods, function (period) {
          return period.current;
        });
      }

      /**
       * Initialize absence types
       */
      function initAbsenceType() {
        if (canViewOrEdit.call(this)) {
          this.selectedAbsenceType = getSelectedAbsenceType.call(this);
        } else {
          // Assign the first absence type to the leave request
          this.selectedAbsenceType = this.absenceTypes[0];
          this.request.type_id = this.selectedAbsenceType.id;
        }

        // Init the `balance` object based on the first absence type
        this.balance.opening = this.selectedAbsenceType.remainder;
      }

      /**
       * Initialize from and to dates and day types.
       * It will also set the day types.
       *
       * @return {Promise}
       */
      function initDates() {
        var deferred = $q.defer(),
          self = this;

        if (canViewOrEdit.call(self)) {
          var attributes = self.request.attributes();

          self.uiOptions.fromDate = self._convertDateFormatFromServer(self.request.from_date);

          self.updateAbsencePeriodDatesTypes.call(self, self.uiOptions.fromDate, 'from')
            .then(function () {
              //to_date and type has been reset in above call so reinitialize from clone
              self.request.to_date = attributes.to_date;
              self.request.to_date_type = attributes.to_date_type;
              self.uiOptions.toDate = self._convertDateFormatFromServer(self.request.to_date);
              self.updateAbsencePeriodDatesTypes.call(self, self.uiOptions.toDate, 'to')
                .then(function () {
                  //resolve only after both from and to day types are also set
                  deferred.resolve();
                });
            });
        } else {
          deferred.resolve();
        }

        return deferred.promise;
      }

      /**
       * Initialize status
       */
      function initStatus() {
        if (canViewOrEdit.call(this)) {
          //set it before self.requestStatuses gets filtered
          this.statusBeforeEdit = getStatusFromValue.call(this, this.request.status_id);

          if (this.isRole('staff')) {
            this.request.status_id = this.requestStatuses['waiting_approval'].value;
          }
        } else if (this.isMode('create')) {
          this.request.status_id = this.isRole('manager') ?
            this.requestStatuses['approved'].value :
            this.requestStatuses['waiting_approval'].value;
        }
      }

      /**
       * Initialize contact
       *
       * {Promise}
       */
      function initContact() {
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
      function loadManagees() {
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
      function loadCommentsAndContactNames() {
        return this.request.loadComments()
          .then(function () {
            //loadComments sets the comments on request object instead of returning it
            this.request.comments.length && loadContactNames.call(this);
          }.bind(this));
      }

      /**
       * Loads unique contact names for all the comments
       *
       * @return {Promise}
       */
      function loadContactNames() {
        var contactIDs = [],
          self = this;

        _.each(self.request.comments, function (comment) {
          //Push only unique contactId's which are not same as logged in user
          if (comment.contact_id != self.directiveOptions.contactId && contactIDs.indexOf(comment.contact_id) === -1) {
            contactIDs.push(comment.contact_id);
          }
        });

        return Contact.all({
          id: { IN: contactIDs }
        }, {
          page: 1,
          size: 0
        }).then(function (contacts) {
          self.comment.contacts = _.indexBy(contacts.list, 'contact_id');
        });
      }

      /**
       * Loads all absence periods
       */
      function loadAbsencePeriods() {
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
      function loadDayTypes() {
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
      function loadStatuses() {
        var self = this;

        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (statuses) {
            self.requestStatuses = _.indexBy(statuses, 'name');
          });
      }

      /**
       * Called after successful submission of leave request
       *
       * @param {String} eventName name of the event to emit
       */
      function postSubmit(eventName) {
        $rootScope.$emit(eventName, this.request);
        this.errors = [];
        // close the modal
        this.ok.call(this);
      }

      /**
       * Helper function to reset pagination for balance breakdow
       */
      function rePaginate() {
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
      function setAbsenceTypesFromEntitlements(absenceTypesAndIds) {
        var self = this;

        return Entitlement.all({
            contact_id: self.request.contact_id,
            period_id: self.period.id,
            type_id: { IN: absenceTypesAndIds.ids }
          }, true) // `true` because we want to use the 'future' balance for calculation
          .then(function (entitlements) {
            if (!entitlements.length) {
              return $q.reject("User has no entitlements");
            }

            // create a list of absence types with a `balance` property
            self.absenceTypes = mapAbsenceTypesWithBalance(absenceTypesAndIds.types, entitlements);
          });
      }

      /**
       * Sets the collection for given day types to sent list of day types,
       * also initializes the day types
       *
       * @param {String} dayType like `from` or `to`
       * @param {Array} listOfDayTypes collection of available day types
       */
      function setDayType(dayType, listOfDayTypes) {
        //will create either of leaveRequestFromDayTypes or leaveRequestToDayTypes key
        var keyForDayTypeCollection = 'request' + _.startCase(dayType) + 'DayTypes';

        this[keyForDayTypeCollection] = listOfDayTypes;

        if (!canViewOrEdit.call(this)) {
          this.request[dayType + '_date_type'] = this[keyForDayTypeCollection][0].value;
        }
      }

      /**
       * Updates the leaverequest
       */
      function updateRequest() {
        var self = this;
        self.submitting = true;

        if (self.isRole('manager')) {
          //if manager has not changed the status then reset status
          if (!self.request.status_id) {
            self.request.status_id = self.statusBeforeEdit.value;
          }
        }

        self.request.isValid()
          .then(function () {
            self.request.update()
              .then(function (result) {
                self.submitting = false;

                if (self.isRole('manager')) {
                  postSubmit.call(self, 'LeaveRequest::updatedByManager');
                } else if (self.isRole('staff')) {
                  postSubmit.call(self, 'LeaveRequest::edit');
                }
              })
              .catch(handleError.bind(self));
          })
          .catch(handleError.bind(self));
      }

      return this;
    }
  ]);
});
