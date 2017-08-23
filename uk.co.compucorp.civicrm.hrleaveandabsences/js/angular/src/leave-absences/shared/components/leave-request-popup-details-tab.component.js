/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/models/session.model',
  'leave-absences/shared/models/calendar-model'
], function (_, moment, components) {
  components.component('leaveRequestPopupDetailsTab', {
    bindings: {
      absencePeriods: '<',
      absenceTypes: '<',
      balance: '=',
      canManage: '<',
      checkSubmitConditions: '=',
      request: '<',
      isLeaveStatus: '<',
      leaveType: '<',
      mode: '<',
      period: '=',
      role: '<',
      selectedAbsenceType: '='
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup-details-tab.html';
    }],
    controllerAs: 'detailsTab',
    controller: DetailsTabController
  });

  DetailsTabController.$inject = ['$controller', '$log', '$rootScope', '$q', 'HR_settings', 'shared-settings', 'Calendar', 'OptionGroup', 'PublicHoliday', 'LeaveRequest'];

  function DetailsTabController ($controller, $log, $rootScope, $q, HRSettings, sharedSettings, Calendar, OptionGroup, PublicHoliday, LeaveRequest) {
    $log.debug('Component: leave-request-popup-details-tab');
    var vm = this;

    vm.statusNames = sharedSettings.statusNames;
    vm.calendar = {};
    vm.errors = [];
    vm.requestDayTypes = [];
    vm.loading = {
      tab: false,
      showBalanceChange: false,
      fromDayTypes: false,
      toDayTypes: false
    };
    vm.pagination = {
      currentPage: 1,
      filteredbreakdown: vm.balance.change.breakdown,
      numPerPage: 7,
      totalItems: vm.balance.change.breakdown.length,
      pageChanged: pageChanged
    };
    vm.uiOptions = {
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

    vm.calculateBalanceChange = calculateBalanceChange;
    vm.changeInNoOfDays = changeInNoOfDays;
    vm.isLeaveType = isLeaveType;
    vm.isMode = isMode;
    vm.isRole = isRole;
    vm.loadAbsencePeriodDatesTypes = loadAbsencePeriodDatesTypes;
    vm.updateAbsencePeriodDatesTypes = updateAbsencePeriodDatesTypes;
    vm.updateBalance = updateBalance;
    vm._canCalculateChange = _canCalculateChange;
    vm._calculateOpeningAndClosingBalance = _calculateOpeningAndClosingBalance;
    vm._checkAndSetAbsencePeriod = _checkAndSetAbsencePeriod;
    vm._convertDateFormatFromServer = _convertDateFormatFromServer;
    vm._convertDateToServerFormat = _convertDateToServerFormat;
    vm._loadCalendar = _loadCalendar;
    vm._reset = _reset;
    vm._setDates = _setDates;
    vm._setDateAndTypes = _setDateAndTypes;
    vm._setMinMaxDate = _setMinMaxDate;

    (function init () {
      $rootScope.$on('LeaveRequestPopup::ContactSelectionComplete', afterContactSelection);
      $controller(_.capitalize(getLeaveType(vm.leaveType, vm.request)) + 'RequestCtrl', { parentCtrl: vm });
      vm.initChildController();
    }());

    function afterContactSelection () {
      vm.loading.tab = true;

      $q.all([
        _loadCalendar(),
        loadDayTypes()
      ])
      .then(initDates)
      .then(function () {
        return $q.all([
          setDaySelectionMode(),
          calculateBalanceChange()
        ]);
      })
      .catch(handleError)
      .finally(function () {
        vm.loading.tab = false;
      });
    }

    /**
     * Calculate change in balance, it updates local balance variables.
     *
     * @return {Promise} empty promise if all required params are not set otherwise promise from server
     */
    function calculateBalanceChange () {
      vm._setDateAndTypes();

      if (!vm._canCalculateChange()) {
        return $q.resolve();
      }

      vm.loading.showBalanceChange = true;
      return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange())
        .then(function (balanceChange) {
          if (balanceChange) {
            vm.balance.change = balanceChange;
            vm._calculateOpeningAndClosingBalance();
            rePaginate();
          }
          vm.loading.showBalanceChange = false;
        })
        .catch(handleError);
    }

    /**
     * Change handler when changing no. of days like Multiple Days or Single Day.
     * It will reset dates, day types, change balance.
     */
    function changeInNoOfDays () {
      vm._reset();
      vm._calculateOpeningAndClosingBalance();
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
      var listToReturn;

      if (!date) {
        return $q.reject([]);
      }

      // Make a copy of the list
      listToReturn = vm.requestDayTypes.slice(0);
      date = vm._convertDateToServerFormat(date);

      return PublicHoliday.isPublicHoliday(date)
        .then(function (result) {
          if (result) {
            return listToReturn.filter(function (publicHoliday) {
              return publicHoliday.name === 'public_holiday';
            });
          }

          return getDayTypesFromDate(date, listToReturn)
            .then(function (inCalendarList) {
              return inCalendarList.length
                ? inCalendarList
                : listToReturn.filter(function (dayType) {
                  return _.includes(['all_day', 'half_day_am', 'half_day_pm'], dayType.name);
                });
            });
        })
        .then(function (listToReturn) {
          setDayType(dayType, listToReturn);

          return listToReturn;
        });
    }

    /**
     * Gets list of day types if its found to be weekend or non working in calendar
     *
     * @param {Date} date to Checks
     * @param {Array} listOfDayTypes array of day types
     * @return {Array} non-empty if found else empty array
     */
    function getDayTypesFromDate (date, listOfDayTypes) {
      date = moment(date);

      return $q.all([
        vm.calendar.isNonWorkingDay(date),
        vm.calendar.isWeekend(date)
      ]).then(function (results) {
        return results[0] ? 'non_working_day' : (results[1] ? 'weekend' : null);
      })
      .then(function (nameFilter) {
        return !nameFilter ? [] : listOfDayTypes.filter(function (day) {
          return day.name === nameFilter;
        });
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
     * Helper function to obtain params for leave request calculateBalanceChange api call
     *
     * @return {Object} containing required keys for leave request
     */
    function getParamsForBalanceChange () {
      return _.pick(vm.request, ['contact_id', 'from_date',
        'from_date_type', 'to_date', 'to_date_type'
      ]);
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
     * Handles errors
     *
     * @param {Array|Object}
     */
    function handleError (errors) {
      $rootScope.$broadcast('LeaveRequestPopup::handleError', _.isArray(errors) ? errors : [errors]);
      vm.loading.fromDayTypes = false;
      vm.loading.showBalanceChange = false;
      vm.loading.tab = false;
      vm.loading.toDayTypes = false;
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
     * Initialize from and to dates and day types.
     * It will also set the day types.
     *
     * @return {Promise}
     */
    function initDates () {
      if (!vm.isMode('create')) {
        var attributes = vm.request.attributes();

        vm.uiOptions.fromDate = vm._convertDateFormatFromServer(vm.request.from_date);

        return vm.loadAbsencePeriodDatesTypes(vm.uiOptions.fromDate, 'from')
          .then(function () {
            // to_date and type has been reset in above call so reinitialize from clone
            vm.request.to_date = attributes.to_date;
            vm.request.to_date_type = attributes.to_date_type;
            vm.uiOptions.toDate = vm._convertDateFormatFromServer(vm.request.to_date);
            return vm.loadAbsencePeriodDatesTypes(vm.uiOptions.toDate, 'to');
          });
      } else {
        return $q.resolve();
      }
    }

    /**
     * Checks if popup is opened in given role
     *
     * @param {String} roleParam like manager, staff
     * @return {Boolean}
     */
    function isRole (roleParam) {
      return vm.role === roleParam;
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
      var oldPeriodId = vm.period.id;
      dayType = dayType || 'from';
      vm.loading[dayType + 'DayTypes'] = true;

      return vm._checkAndSetAbsencePeriod(date)
        .then(function () {
          var isInCurrentPeriod = oldPeriodId === vm.period.id;

          if (!isInCurrentPeriod) {
            // partial reset is required when user has selected a to date and
            // then changes absence period from from date
            // no reset required for single days and to date changes
            if (vm.uiOptions.multipleDays && dayType === 'from') {
              vm.uiOptions.showBalance = false;
              vm.uiOptions.toDate = null;
              vm.request.to_date = null;
              vm.request.to_date_type = null;
            }

            return $q.all([
              vm._loadCalendar()
            ]);
          }
        })
        .then(function () {
          vm._setMinMaxDate();

          return filterLeaveRequestDayTypes(date, dayType);
        })
        .finally(function () {
          /**
           * after the request is completed fromDayTypes or toDayTypes are
           * set to false and the corresponding field is shown on the ui.
           */
          vm.loading[dayType + 'DayTypes'] = false;
        });
    }

    /**
     * Initializes leave request day types
     *
     * @return {Promise}
     */
    function loadDayTypes () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (dayTypes) {
          vm.requestDayTypes = dayTypes;
        });
    }

    /**
     * It filters the breakdown to obtain the ones for currently selected page.
     */
    function pageChanged () {
      var begin = (vm.pagination.currentPage - 1) * vm.pagination.numPerPage;
      var end = begin + vm.pagination.numPerPage;

      vm.pagination.filteredbreakdown = vm.balance.change.breakdown.slice(begin, end);
    }

    /**
     * Helper function to reset pagination for balance breakdow
     */
    function rePaginate () {
      vm.pagination.totalItems = vm.balance.change.breakdown.length;
      vm.pagination.filteredbreakdown = vm.balance.change.breakdown;
      vm.pagination.pageChanged();
    }

    /**
     * Sets day selection mode: multiple days or a single day
     */
    function setDaySelectionMode () {
      if ((vm.isMode('edit') && vm.request.from_date === vm.request.to_date) ||
        (vm.isMode('create') && vm.isLeaveType('sickness'))) {
        vm.uiOptions.multipleDays = false;
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

      vm[keyForDayTypeCollection] = listOfDayTypes;

      if (vm.isMode('create')) {
        vm.request[dayType + '_date_type'] = vm[keyForDayTypeCollection][0].value;
      }
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
      return vm.loadAbsencePeriodDatesTypes(date, dayType)
        .then(function () {
          return vm.updateBalance();
        })
        .catch(function (errors) {
          handleError(errors);
          vm._setDateAndTypes();
        });
    }

    /**
     * Whenever the absence type changes, update the balance opening.
     * Also the balance change needs to be recalculated, if the `from` and `to`
     * dates have been already selected
     */
    function updateBalance () {
      vm.selectedAbsenceType = getSelectedAbsenceType();
      // get the `balance` of the newly selected absence type
      vm.balance.opening = vm.selectedAbsenceType.remainder;

      vm.calculateBalanceChange();
    }

    function _canCalculateChange () {
      return !!vm.request.from_date && !!vm.request.to_date &&
        !!vm.request.from_date_type && !!vm.request.to_date_type;
    }

    /**
     * Calculates and updates opening and closing balances
     */
    function _calculateOpeningAndClosingBalance () {
      vm.balance.opening = vm.selectedAbsenceType.remainder;
      // the change is negative so adding it will actually subtract it
      vm.balance.closing = vm.balance.opening + vm.balance.change.amount;
    }

    /**
     * Finds if date is in any absence period and sets absence period for the given date
     *
     * @param {Date/String} date
     * @return {Promise} with true value if period found else rejected false
     */
    function _checkAndSetAbsencePeriod (date) {
      var formattedDate = moment(date).format(vm.uiOptions.userDateFormat.toUpperCase());

      vm.period = _.find(vm.absencePeriods, function (period) {
        return period.isInPeriod(formattedDate);
      });

      if (!vm.period) {
        vm.period = {};
        // inform user if absence period is not found
        vm.loading['fromDayTypes'] = false;
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
     * Initializes user's calendar (work patterns)
     *
     * @return {Promise}
     */
    function _loadCalendar () {
      return Calendar.get(vm.request.contact_id, vm.period.start_date, vm.period.end_date)
        .then(function (usersCalendar) {
          vm.calendar = usersCalendar;
        });
    }

    /**
     * Sets dates and types for vm.request from UI
     */
    function _setDates () {
      vm.request.from_date = vm.uiOptions.fromDate ? vm._convertDateToServerFormat(vm.uiOptions.fromDate) : null;
      vm.request.to_date = vm.uiOptions.toDate ? vm._convertDateToServerFormat(vm.uiOptions.toDate) : null;

      if (!vm.uiOptions.multipleDays && vm.uiOptions.fromDate) {
        vm.uiOptions.toDate = vm.uiOptions.fromDate;
        vm.request.to_date = vm.request.from_date;
      }
    }

    /**
     * Sets dates and types for vm.request from UI
     */
    function _setDateAndTypes () {
      vm._setDates();

      if (vm.uiOptions.multipleDays) {
        vm.uiOptions.showBalance = !!vm.request.from_date && !!vm.request.from_date_type && !!vm.request.to_date && !!vm.request.to_date_type && !!vm.period.id;
      } else {
        if (vm.uiOptions.fromDate) {
          vm.request.to_date_type = vm.request.from_date_type;
        }

        vm.uiOptions.showBalance = !!vm.request.from_date && !!vm.request.from_date_type && !!vm.period.id;
      }
    }

    /**
     * Sets the min and max for to date from absence period. It also sets the
     * init/starting date which user can select from. For multiple days request
     * user can select to date which is one more than the the start date.
     */
    function _setMinMaxDate () {
      if (vm.uiOptions.fromDate) {
        var nextFromDay = moment(vm.uiOptions.fromDate).add(1, 'd').toDate();

        vm.uiOptions.date.to.options.minDate = nextFromDay;
        vm.uiOptions.date.to.options.initDate = nextFromDay;

        // also re-set to date if from date is changing and less than to date
        if (vm.uiOptions.toDate && moment(vm.uiOptions.toDate).isBefore(vm.uiOptions.fromDate)) {
          vm.uiOptions.toDate = vm.uiOptions.fromDate;
        }
      } else {
        vm.uiOptions.date.to.options.minDate = vm._convertDateFormatFromServer(vm.period.start_date);
        vm.uiOptions.date.to.options.initDate = vm.uiOptions.date.to.options.minDate;
      }

      vm.uiOptions.date.to.options.maxDate = vm._convertDateFormatFromServer(vm.period.end_date);
    }

    /**
     * Resets data in dates, types, balance.
     */
    function _reset () {
      vm.uiOptions.toDate = vm.uiOptions.fromDate;
      vm.request.to_date_type = vm.request.from_date_type;
      vm.request.to_date = vm.request.from_date;

      vm.calculateBalanceChange();
    }
  }
});
