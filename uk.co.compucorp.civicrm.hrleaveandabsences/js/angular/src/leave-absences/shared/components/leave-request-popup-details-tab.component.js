/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/models/session.model',
  'leave-absences/shared/models/calendar.model'
], function (_, moment, components) {
  components.component('leaveRequestPopupDetailsTab', {
    bindings: {
      absencePeriods: '<',
      absenceTypes: '<',
      balance: '=',
      request: '<',
      isLeaveStatus: '<',
      leaveType: '<',
      isMode: '<',
      isSelfRecord: '<',
      period: '=',
      isRole: '<',
      selectedAbsenceType: '=',
      forceRecalculateBalanceChange: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-popup/leave-request-popup-details-tab.html';
    }],
    controllerAs: 'detailsTab',
    controller: DetailsTabController
  });

  DetailsTabController.$inject = ['$controller', '$log', '$rootScope', '$scope',
    '$q', 'HR_settings', 'shared-settings', 'Calendar', 'OptionGroup',
    'LeaveRequest', '$timeout'];

  function DetailsTabController ($controller, $log, $rootScope, $scope,
    $q, HRSettings, sharedSettings, Calendar, OptionGroup,
    LeaveRequest, $timeout) {
    $log.debug('Component: leave-request-popup-details-tab');
    var originalOpeningBalance = null;
    var listeners = [];
    var vm = this;

    vm.canManage = false;
    vm.calendar = {};
    vm.errors = [];
    vm.isRequired = true;
    vm.requestDayTypes = [];
    vm.statusNames = sharedSettings.statusNames;
    vm.loading = {
      tab: false,
      balanceChange: false,
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
      },
      times: {
        from: {
          amount: 0,
          amountExpanded: false,
          loading: false,
          max: '00:00',
          maxAmount: 0,
          min: '00:00',
          time: ''
        },
        to: {
          amount: 0,
          amountExpanded: false,
          loading: false,
          max: '00:00',
          maxAmount: 0,
          min: '00:00',
          time: ''
        }
      },
      time_interval: 15 // 15 minutes intervals in time and deduction inputs
    };

    vm.convertDateFormatFromServer = convertDateFormatFromServer;
    vm.convertDateToServerFormat = convertDateToServerFormat;
    vm.dateChangeHandler = dateChangeHandler;
    vm.dateTypeChangeHandler = dateTypeChangeHandler;
    vm.daysSelectionModeChangeHandler = daysSelectionModeChangeHandler;
    vm.disableAndShowLoadingTimeInput = disableAndShowLoadingTimeInput;
    vm.getMomentDateWithGivenTime = getMomentDateWithGivenTime;
    vm.handleError = handleError;
    vm.isCalculationUnit = isCalculationUnit;
    vm.isLeaveType = isLeaveType;
    vm.isNotWorkingDay = isNotWorkingDay;
    vm.performBalanceChangeCalculation = performBalanceChangeCalculation;
    vm.setRequestDateTimesAndDateTypes = setRequestDateTimesAndDateTypes;
    vm.updateEndTimeInputMinTime = updateEndTimeInputMinTime;
    vm.$onDestroy = unsubscribeFromEvents;

    (function init () {
      $controller(
        'RequestModalDetails' + _.capitalize(getLeaveType(vm.leaveType, vm.request)) + 'Controller',
        { detailsController: vm }
      );

      vm.canManage = vm.isRole('manager') || vm.isRole('admin');
      vm.loading.tab = true;

      $scope.$emit('LeaveRequestPopup::addTab', vm);
      initListeners();

      vm.initChildController()
        .then(function () {
          return $q.all([
            loadCalendar(),
            loadDayTypes()
          ]);
        })
        .then(!vm.isMode('create') && initDates)
        .then(setDaysSelectionMode)
        .then(function () {
          if (!vm.isMode('create')) {
            return $q.resolve()
              .then(vm.initTimesExtended)
              .then(setRequestDateTimesAndDateTypes);
          }
        })
        .then(!vm.isMode('create') && setDatepickerBoundariesForToDate)
        .then(initOriginalOpeningBalance)
        .then(setOpeningBalance)
        .then(initBalanceChange)
        .then(initFromTimeWatcher)
        .then(!vm.isMode('view') && vm.initWatchersExtended)
        .catch(handleError)
        .finally(function () {
          vm.loading.tab = false;
        });
    }());

    /**
     * Calculates closing balance which is opening balance minus change amount.
     * We use "+" operation since the change amount is negative.
     */
    function calculateClosingBalance () {
      vm.balance.closing = vm.balance.opening + vm.balance.change.amount;
    }

    /**
     * Converts given date to javascript date as expected by uib-datepicker
     *
     * @param {String} date from server
     * @return {Date}
     */
    function convertDateFormatFromServer (date) {
      return moment(date, sharedSettings.serverDateFormat).toDate();
    }

    /**
     * Converts given date to server format
     *
     * @param {Date} date
     * @return {String} date converted to server format
     */
    function convertDateToServerFormat (date) {
      return moment(date).format(sharedSettings.serverDateFormat);
    }

    /**
     * Sets dates (with time in case of "hours" Absence Type) from UI to request
     *
     * @param  {String} dateType from|to
     * @return {Promise}
     */
    function dateChangeHandler (dateType) {
      var absencePeriodChanged;

      return $q.resolve()
        .then(function () {
          resetUIInputs(dateType);

          if (dateType === 'from') {
            resetToDateIfGreaterThanFromDate();
          }

          if (isCalculationUnit('hours')) {
            disableAndShowLoadingTimeInput(dateType);

            if (dateType === 'from' && !vm.uiOptions.multipleDays) {
              disableAndShowLoadingTimeInput('to');
            }
          }

          vm.loading[dateType + 'DayTypes'] = true;
        })
        .then(function () {
          return getAbsencePeriod(dateType);
        })
        .then(function (datePeriod) {
          absencePeriodChanged = datePeriod.id !== vm.period.id;

          if (absencePeriodChanged) {
            vm.period = datePeriod;
          }

          // @TODO this exception should belong to getAbsencePeriod()
          if (!vm.period.id) {
            return $q.reject('Please change date as it is not in any absence period');
          }
        })
        .then(setDatepickerBoundariesForToDate)
        .then(function () {
          if (absencePeriodChanged) {
            return loadCalendar();
          }
        })
        .then(function () {
          return vm.onDateChangeExtended && vm.onDateChangeExtended(dateType);
        })
        .then(function () {
          setRequestDateTimesAndDateTypes();

          if (absencePeriodChanged) {
            $rootScope.$broadcast('LeaveRequestPopup::absencePeriodChanged');
          } else {
            return performBalanceChangeCalculation();
          }
        })
        .catch(handleError)
        .finally(function () {
          vm.loading[dateType + 'DayTypes'] = false;

          if (isCalculationUnit('hours')) {
            vm.uiOptions.times.from.loading = false;
            vm.uiOptions.times.to.loading = false;
          }
        });
    }

    /**
     * Sets day types from UI to request and performs balance calculation
     *
     * @return {Promise}
     */
    function dateTypeChangeHandler () {
      setRequestDateTimesAndDateTypes();

      return performBalanceChangeCalculation();
    }

    /**
     * Handles the change of days selection mode (single day, multiple days).
     * Always flushes "to" date.
     * Only performs calculation if switched to Single day mode.
     *
     * @return {Promise}
     */
    function daysSelectionModeChangeHandler () {
      vm.uiOptions.toDate = null;

      resetUIInputs('to');

      return $q.resolve()
        .then(setRequestDateTimesAndDateTypes)
        .then(vm.setDaysSelectionModeExtended)
        .then(!vm.uiOptions.multipleDays && performBalanceChangeCalculation);
    }

    /**
     * Disables a time input of the specified type
     * and shows that it is currently loading
     *
     * @param {String} type from|to
     */
    function disableAndShowLoadingTimeInput (type) {
      var timeObject = vm.uiOptions.times[type];

      timeObject.loading = true;
      timeObject.disabled = true;
    }

    /**
     * Gets absence period for the given date
     *
     * @param  {String} dateType from|to
     * @return {Object} Absence period instance or empty object if not found
     */
    function getAbsencePeriod (dateType) {
      var formattedDate = moment(vm.uiOptions[dateType + 'Date'])
        .format(vm.uiOptions.userDateFormat.toUpperCase());

      return _.find(vm.absencePeriods, function (period) {
        return period.isInPeriod(formattedDate);
      }) || {};
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
     * Returns a date with a given time
     *
     * @param  {String} time in HH:mm or hh:mm formats
     * @return {Moment}
     */
    function getMomentDateWithGivenTime (time) {
      return moment()
        .set({
          'hours': time.split(':')[0],
          'minutes': time.split(':')[1]
        });
    }

    /**
     * Gets original balance change breakdown that cannot be affected by,
     * for example, work pattern changes.
     *
     * @return {Promise}
     */
    function getOriginalBalanceChange () {
      toggleBalance();

      vm.loading.balanceChange = true;

      return vm.request.getBalanceChangeBreakdown()
        .then(setBalanceChange)
        .catch(handleError);
    }

    /**
     * Handles errors
     *
     * @param {Array|Object}
     */
    function handleError (errors) {
      $rootScope.$broadcast('LeaveRequestPopup::handleError', _.isArray(errors) ? errors : [errors]);
      vm.loading.fromDayTypes = false;
      vm.loading.balanceChange = false;
      vm.loading.tab = false;
      vm.loading.toDayTypes = false;
    }

    /**
     * Initiates the balance change breakdown
     *
     * @return {Promise}
     */
    function initBalanceChange () {
      return !vm.isMode('create') && !vm.forceRecalculateBalanceChange
        ? getOriginalBalanceChange()
        : performBalanceChangeCalculation();
    }

    /**
     * Initialises and sets the "from" and "to" dates and day types
     *
     * @return {Promise}
     */
    function initDates () {
      var attributes = vm.request.attributes();

      vm.uiOptions.fromDate = convertDateFormatFromServer(vm.request.from_date);
      // to_date and type has been reset in above call so reinitialize from clone
      vm.request.to_date = attributes.to_date;
      vm.request.to_date_type = attributes.to_date_type;
      vm.uiOptions.toDate = convertDateFormatFromServer(vm.request.to_date);

      return vm.initDayTypesExtended ? vm.initDayTypesExtended() : $q.resolve();
    }

    /**
     * Initialises listeners
     */
    function initListeners () {
      listeners.push(
        $rootScope.$on('LeaveRequestPopup::absenceTypeChanged', function () {
          updateAbsenceType();
        }),
        // @TODO handle when absence period exists, but there are no entitlements for this period
        // @TODO absence types with balances should be watched via $onChange
        $rootScope.$on('LeaveRequestPopup::absencePeriodBalancesUpdated', function (event, absenceTypesWithBalances) {
          updateBalances(absenceTypesWithBalances);
        }),
        $rootScope.$on('LeaveRequestPopup::recalculateBalanceChange', performBalanceChangeCalculation)
      );
    }

    /**
     * Initialize the original opening balance when in edit mode and the
     * request is approved. This allows to display the opening balance before
     * the request was created.
     *
     * The formula is absence type remainder + balance change. Since
     * Balance Change is a negative number so it needs to be subtracted.
     */
    function initOriginalOpeningBalance () {
      if (vm.isMode('edit') && (
        vm.isLeaveStatus(sharedSettings.statusNames.approved) ||
        vm.isLeaveStatus(sharedSettings.statusNames.adminApproved)
      )) {
        originalOpeningBalance = {
          absenceTypeId: vm.request.type_id,
          value: vm.selectedAbsenceType.remainder - vm.request.balance_change
        };
      }
    }

    /**
     * Initialises watcher for from time values.
     * The value of the from time input affects the min value of the to time input
     */
    function initFromTimeWatcher () {
      $rootScope.$watch(
        function () {
          return vm.uiOptions.times.from.time;
        },
        function (newValue, oldValue) {
          if (newValue === oldValue) {
            return;
          }

          if (!vm.uiOptions.multipleDays) {
            updateEndTimeInputMinTime(newValue);
            setRequestDateTimesAndDateTypes();
          }
        });
    }

    /**
     * Checks for the calculation unit for the selected absence type
     *
     * @param  {String} unit (days|hours)
     * @return {Boolean}
     */
    function isCalculationUnit (unit) {
      return vm.selectedAbsenceType.calculation_unit_name === unit;
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
     * Checks if the given day type is not a working day.
     * Do not confuse with "non working day".
     *
     * @param  {String} dayType
     * @return {Boolean} true if is not a working day
     */
    function isNotWorkingDay (dayType) {
      return _.includes(['weekend', 'non_working_day', 'public_holiday'], dayType);
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
     * Initializes user's calendar (work patterns)
     *
     * @return {Promise}
     */
    function loadCalendar () {
      return Calendar.get(vm.request.contact_id, vm.period.start_date, vm.period.end_date)
        .then(function (contactCalendar) {
          vm.calendar = contactCalendar;
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
     * Performs a set of manipulations to prepare all data for calculation and
     * then triggers the calculation itself if it can be calculated
     *
     * @return {Promise}
     */
    function performBalanceChangeCalculation () {
      toggleBalance();

      if (!vm.canCalculateChange()) {
        return $q.resolve();
      }

      vm.loading.balanceChange = true;
      vm.request.change_balance = true;

      return vm.calculateBalanceChange()
        .then(setBalanceChange)
        .catch(handleError)
        .finally(function () {
          vm.loading.balanceChange = false;
        });
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
     * Checks if the given request has same "from" and "to" dates
     *
     * @return {Boolean}
     */
    function requestHasSameDates () {
      return convertDateToServerFormat(vm.request.from_date) ===
        convertDateToServerFormat(vm.request.to_date);
    }

    /**
     * Checks To Date and flushes it if it is the same or before
     * the "from date" in case of the multiple day request.
     */
    function resetToDateIfGreaterThanFromDate () {
      if (
        vm.uiOptions.toDate && vm.uiOptions.fromDate && vm.uiOptions.multipleDays &&
        moment(vm.uiOptions.toDate).isSameOrBefore(vm.uiOptions.fromDate)
      ) {
        vm.uiOptions.toDate = null;

        resetUIInputs('to');
      }
    }

    /**
     * Reset day types, times and deductions.
     *
     * @param {String} dateType from|to
     */
    function resetUIInputs (dateType) {
      var time = vm.uiOptions.times[dateType];

      vm['request' + _.startCase(dateType) + 'DayTypes'] = [];
      time.loading = false;

      setRequestDateTimesAndDateTypes();
      (vm.resetUIInputsExtended) && vm.resetUIInputsExtended(dateType);
      toggleBalance();
    }

    /**
     * Sets balance change breakdown after it was retrieved or calculated
     *
     * @param {Object} balanceChange
     */
    function setBalanceChange (balanceChange) {
      vm.balance.change = balanceChange;
      vm.request.balance_change = balanceChange.amount;

      calculateClosingBalance();
      rePaginate();

      vm.loading.balanceChange = false;
    }

    /**
     * Sets the min and max for to date from absence period. It also sets the
     * init/starting date which user can select from. For multiple days request
     * user can select to date which is one more than the the start date.
     */
    function setDatepickerBoundariesForToDate () {
      var dayAfterFromDate, initDate, minDate;

      if (vm.uiOptions.fromDate) {
        dayAfterFromDate = moment(vm.uiOptions.fromDate).add(1, 'day').toDate();
        minDate = dayAfterFromDate;
        initDate = dayAfterFromDate;
      } else {
        minDate = convertDateFormatFromServer(vm.period.start_date);
        initDate = vm.uiOptions.date.to.options.minDate;
      }

      vm.uiOptions.date.to.options.initDate = initDate;
      vm.uiOptions.date.to.options.minDate = minDate;
      vm.uiOptions.date.to.options.maxDate = convertDateFormatFromServer(vm.period.end_date);
    }

    /**
     * Sets day selection mode: multiple days or a single day
     *
     * @return {Promise}
     */
    function setDaysSelectionMode () {
      if ((!vm.isMode('create') && requestHasSameDates()) ||
        (vm.isMode('create') && (isLeaveType('sickness') || isCalculationUnit('hours')))) {
        vm.uiOptions.multipleDays = false;
      } else {
        vm.uiOptions.multipleDays = true;
      }

      return $q.resolve().then(vm.setDaysSelectionModeExtended);
    }

    /**
     * Updates the opening balance depending on the mode
     * In case of "edit" or "view" mode, sets original balance
     * In case of "create", sets the balance from the remainder
     */
    function setOpeningBalance () {
      if (originalOpeningBalance &&
        originalOpeningBalance.absenceTypeId === vm.selectedAbsenceType.id) {
        vm.balance.opening = originalOpeningBalance.value || 0;
      } else {
        vm.balance.opening = vm.selectedAbsenceType.remainder;
      }
    }

    /**
     * Sets currently selected absence type from leave request type_id
     */
    function setSelectedAbsenceType () {
      vm.selectedAbsenceType = _.find(vm.absenceTypes, function (absenceType) {
        return absenceType.id === vm.request.type_id;
      });
    }

    /**
     * Sets dates, times and date types to request from UI in the DateTime format
     */
    function setRequestDateTimesAndDateTypes () {
      var options = vm.uiOptions;
      var times = options.times;

      vm.request.from_date = options.fromDate ? convertDateToServerFormat(options.fromDate) : null;
      vm.request.to_date = options.toDate ? convertDateToServerFormat(options.toDate) : null;

      if (!options.multipleDays && options.fromDate) {
        vm.request.to_date = vm.request.from_date;
        vm.request.to_date_type = vm.request.from_date_type;
      }

      if (isCalculationUnit('hours') || isLeaveType('toil')) {
        vm.request.from_date = vm.request.from_date && times.from.time ? vm.request.from_date + ' ' + times.from.time : null;
        vm.request.to_date = vm.request.to_date && times.to.time ? vm.request.to_date + ' ' + times.to.time : null;
      }
    }

    /**
     * Shows or hides the balance breakdown depending on various conditions
     */
    function toggleBalance () {
      vm.uiOptions.showBalance = vm.canCalculateChange();
    }

    /**
     * Destroys all event listeners
     */
    function unsubscribeFromEvents () {
      _.forEach(listeners, function (listener) {
        listener();
      });
    }

    /**
     * Updates absence types, sets opening balance for the selected absence type,
     * and, if calculation unit has changed, changes the days selection modes,
     * loads time ranges if the absence type calculation unit is "hours",
     * updates the dates and times in the request and finally, performs
     * balance change calculation
     *
     * @return {Promise}
     */
    function updateAbsenceType () {
      var absenceTypeUnitChanged;
      var previousAbsenceTypeUnit = vm.selectedAbsenceType.calculation_unit_name;

      setSelectedAbsenceType();

      absenceTypeUnitChanged = previousAbsenceTypeUnit !== vm.selectedAbsenceType.calculation_unit_name;

      setOpeningBalance();

      return $q.resolve()
        .then(absenceTypeUnitChanged && setDaysSelectionMode)
        .then(function () {
          if (absenceTypeUnitChanged) {
            return vm.onAbsenceTypeUpdateExtended
              ? vm.onAbsenceTypeUpdateExtended()
              : $q.resolve();
          }
        })
        .then(absenceTypeUnitChanged && setRequestDateTimesAndDateTypes)
        .then(performBalanceChangeCalculation);
    }

    /**
     * Updates balances as per the absence types with balances passed,
     * resets "to" date, time and deduction,
     * sets opening balance and finally performs balance change calculation
     *
     * @param  {Object} absenceTypesWithBalances
     * @return {Promise}
     */
    function updateBalances (absenceTypesWithBalances) {
      vm.absenceTypes = absenceTypesWithBalances;

      // @TODO watching and setting selected absence type should go to $onChange
      setSelectedAbsenceType();

      if (moment(vm.uiOptions.toDate).isAfter(vm.period.end_date)) {
        vm.uiOptions.toDate = undefined;

        resetUIInputs('to');
      }

      setOpeningBalance();

      return performBalanceChangeCalculation();
    }

    /**
     * Updates minimum allowed time for end time input basing on start time
     * and flushes end time in case it is out of boundaries
     *
     * @param {String} time - start time in HH:mm format
     */
    function updateEndTimeInputMinTime (time) {
      var timeToMin = getMomentDateWithGivenTime(time)
        .add(vm.uiOptions.time_interval, 'minutes');

      if (timeToMin.isAfter(getMomentDateWithGivenTime(vm.uiOptions.times.to.max))) {
        return;
      }

      vm.uiOptions.times.to.min = timeToMin.format('HH:mm');

      if (timeToMin.isAfter(getMomentDateWithGivenTime(vm.uiOptions.times.to.time))) {
        vm.uiOptions.times.to.time = '';
      }
    }
  }
});
