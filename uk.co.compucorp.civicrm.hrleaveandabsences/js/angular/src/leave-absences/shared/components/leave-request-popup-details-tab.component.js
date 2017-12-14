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
      checkSubmitConditions: '=',
      request: '<',
      isLeaveStatus: '<',
      leaveType: '<',
      isMode: '<',
      isSelfRecord: '<',
      period: '=',
      isRole: '<',
      selectedAbsenceType: '='
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-popup/leave-request-popup-details-tab.html';
    }],
    controllerAs: 'detailsTab',
    controller: DetailsTabController
  });

  DetailsTabController.$inject = ['$controller', '$log', '$rootScope', '$scope',
    '$q', 'HR_settings', 'shared-settings', 'Calendar', 'OptionGroup',
    'PublicHoliday', 'LeaveRequest', '$timeout'];

  function DetailsTabController ($controller, $log, $rootScope, $scope,
    $q, HRSettings, sharedSettings, Calendar, OptionGroup,
    PublicHoliday, LeaveRequest, $timeout) {
    $log.debug('Component: leave-request-popup-details-tab');
    var originalOpeningBalance = null;
    var listeners = [];
    var vm = this;

    vm.canManage = false;
    vm.calendar = {};
    vm.errors = [];
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
          time: '',
          min: '00:00',
          max: '00:00',
          amount: 0,
          maxAmount: 0,
          disabled: true,
          loading: false
        },
        to: {
          time: '',
          min: '00:00',
          max: '00:00',
          amount: 0,
          maxAmount: 0,
          disabled: true,
          loading: false
        }
      }
    };

    vm.convertDateFormatFromServer = convertDateFormatFromServer;
    vm.convertDateToServerFormat = convertDateToServerFormat;
    vm.daysSelectionModeChangeHandler = daysSelectionModeChangeHandler;
    vm.isLeaveType = isLeaveType;
    vm.isNotWorkingDay = isNotWorkingDay;
    vm.performBalanceChangeCalculation = performBalanceChangeCalculation;
    vm.setDate = setDate;
    vm.$onDestroy = unsubscribeFromEvents;

    (function init () {
      $controller(
        'RequestModalDetails' + _.capitalize(getLeaveType(vm.leaveType, vm.request)) + 'Controller',
        { detailsController: vm }
      );

      vm.canManage = vm.isRole('manager') || vm.isRole('admin');
      vm.loading.tab = true;
      initListeners();

      vm.initChildController()
      .then(function () {
        return $q.all([
          loadCalendar(),
          loadDayTypes()
        ]);
      })
      .then(!vm.isMode('create') && initDates)
      .then(function () {
        if (!vm.isMode('create') && isCalculationUnit('hours')) {
          return initTimes();
        }
      })
      .then(!vm.isMode('create') && setMinMaxDatesToUI)
      .then(initOriginalOpeningBalance)
      .then(setOpeningBalance)
      .then(function () {
        return $q.all([
          setDaysSelectionMode(),
          initBalanceChange()
        ]);
      })
      .then(initTimeAndDateInputsWatchers)
      .catch(handleError)
      .finally(function () {
        vm.loading.tab = false;
      });
    }());

    /**
     * Checks To Date and flushes it if it is the same or before
     * the "from date" in case of the multiple day request.
     */
    function checkToDate () {
      if (
        vm.uiOptions.toDate && vm.uiOptions.fromDate && vm.uiOptions.multipleDays &&
        moment(vm.uiOptions.toDate).isSameOrBefore(vm.uiOptions.fromDate)
      ) {
        vm.uiOptions.toDate = null;

        resetUIDayTypesTimeAndDeductions('to');
      }
    }

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
     * Handles the change of days selection mode (single day, multiple days)
     *
     * @return {Promise}
     */
    function daysSelectionModeChangeHandler () {
      checkToDate();
      setRequestDates();

      return performBalanceChangeCalculation()
        .then(vm.setDaysSelectionModeExtended);
    }

    /**
     * Extracts time from server formatted date
     *
     * @param  {String} date in "YYYY-MM-DD hh:mm:ss" format
     * @return {String} time in hh:mm format
     */
    function extractTimeFromServerDate (date) {
      return moment(date).format('HH:mm');
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
      if (!date) {
        return $q.reject([]);
      }

      date = convertDateToServerFormat(date);

      return PublicHoliday.isPublicHoliday(date)
        .then(function (result) {
          if (result) {
            return vm.requestDayTypes.filter(function (publicHoliday) {
              return publicHoliday.name === 'public_holiday';
            });
          }

          return getDayTypesFromDate(date, vm.requestDayTypes)
            .then(function (inCalendarList) {
              return inCalendarList.length
                ? inCalendarList
                : vm.requestDayTypes.filter(function (dayType) {
                  return _.includes(['all_day', 'half_day_am', 'half_day_pm'], dayType.name);
                });
            });
        })
        .then(function (dayTypes) {
          setDayTypes(dayType, dayTypes);

          return dayTypes;
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
      return (!vm.isMode('create') ? getOriginalBalanceChange() : performBalanceChangeCalculation());
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
     * Initialises and sets the "from" and "to" dates and day types
     *
     * @return {Promise}
     */
    function initDates () {
      var attributes = vm.request.attributes();

      vm.uiOptions.fromDate = convertDateFormatFromServer(vm.request.from_date);

      return loadDayTypesForDate(vm.uiOptions.fromDate, 'from')
        .then(function () {
          // to_date and type has been reset in above call so reinitialize from clone
          vm.request.to_date = attributes.to_date;
          vm.request.to_date_type = attributes.to_date_type;
          vm.uiOptions.toDate = convertDateFormatFromServer(vm.request.to_date);

          return loadDayTypesForDate(vm.uiOptions.toDate, 'to');
        });
    }

    /**
     * Initialises listeners
     */
    function initListeners () {
      listeners.push(
        $rootScope.$on('LeaveRequestPopup::updateAbsenceType', function () {
          var absenceTypeUnitChanged;
          var previousAbsenceTypeUnit = vm.selectedAbsenceType.calculation_unit_name;

          vm.selectedAbsenceType = getSelectedAbsenceType();
          absenceTypeUnitChanged = previousAbsenceTypeUnit !== vm.selectedAbsenceType.calculation_unit_name;

          setOpeningBalance();

          return $q.resolve()
            .then(absenceTypeUnitChanged && setDaysSelectionMode)
            .then(function () {
              if (absenceTypeUnitChanged && isCalculationUnit('hours')) {
                return loadTimeRangesFromWorkPattern('from');
              }
            })
            .then(setRequestDates)
            .then(performBalanceChangeCalculation);
        }),
        $rootScope.$on('LeaveRequestPopup::updateBalance', function (event, absenceTypesWithBalances) {
          vm.absenceTypes = absenceTypesWithBalances;
          vm.selectedAbsenceType = getSelectedAbsenceType();

          if (moment(vm.uiOptions.toDate).isAfter(vm.period.end_date)) {
            vm.uiOptions.toDate = undefined;

            resetUIDayTypesTimeAndDeductions('to');
          }

          setOpeningBalance();
          performBalanceChangeCalculation();
        })
      );
    }

    /**
     * Initialises and sets the "from" and "to" times
     */
    function initTimes () {
      var times = vm.uiOptions.times;
      var request = vm.request;

      if (!vm.isMode('create') && isCalculationUnit('hours')) {
        return $q.all(['from', 'to'].map(function (type) {
          return loadTimeRangesFromWorkPattern(type)
            .then(function () {
              times[type].time = extractTimeFromServerDate(request[type + '_date']);
              times[type].amount = Math.min(request[type + '_date_amount'], times[type].maxAmount).toString();
            });
        })).then(setRequestHoursDeductions);
      }
    }

    /**
     * Initialises watchers for time and date inputs
     */
    function initTimeAndDateInputsWatchers () {
      if (vm.isMode('view') || isLeaveType('toil')) {
        return;
      }

      _.each(['from', 'to'], function (type) {
        $scope.$watch('detailsTab.uiOptions.times.' + type + '.amount', function (amount, oldAmount) {
          if (!isCalculationUnit('days') && +amount !== +oldAmount) {
            setRequestHoursDeductions();
            vm.performBalanceChangeCalculation();
          }
        });
        $scope.$watch('detailsTab.uiOptions.times.' + type + '.time', function (time, oldTime) {
          if (!isCalculationUnit('days') && time !== oldTime) {
            setRequestDates();
          }
        });
      });
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
     * Loads absence types and calendar data on component initialization and
     * when they need to be updated.
     *
     * @param {Date} date - the selected date
     * @param {String} dayType - set to from if from date is selected else to
     * @return {Promise}
     */
    function loadDayTypesForDate (date, dateType) {
      return filterLeaveRequestDayTypes(date, dateType)
        .then(function () {
          vm.loading[dateType + 'DayTypes'] = false;
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
     * Loads and sets time ranges from work pattern for "from" or "to" timepickers
     *
     * @param  {String} type (days|hours)
     * @return {Promise}
     */
    function loadTimeRangesFromWorkPattern (type) {
      var date = vm.uiOptions[type + 'Date'];
      var timeObject = vm.uiOptions.times[type];

      if (!date) {
        return $q.resolve();
      }

      timeObject.loading = true;
      timeObject.disabled = true;

      return vm.request.getWorkDayForDate(date)
        .then(function (response) {
          timeObject.min = response.time_from || '00:00';
          timeObject.max = response.time_to || '00:00';
          timeObject.maxAmount = response.number_of_hours.toString() || '0';
          timeObject.time = (type === 'to' ? timeObject.max : timeObject.min);
          timeObject.amount = timeObject.maxAmount;
          timeObject.disabled = false;
        })
        .catch(handleError)
        .finally(function () {
          timeObject.loading = false;
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
     * Checks absence period for the changed date,
     * updates the entitlements if needed
     * and notifies user if the Absence Period was not found
     *
     * @param {String} dateType from|to
     * @return {Promise}
     */
    function setAbsencePeriod (dateType) {
      var periodHasBeenChanged;

      return isAbsencePeriodChanged(dateType)
        .then(function (_periodHasBeenChanged_) {
          periodHasBeenChanged = _periodHasBeenChanged_;

          return periodHasBeenChanged && loadCalendar();
        })
        .then(function () {
          return $q.all([
            isCalculationUnit('hours') && loadTimeRangesFromWorkPattern(dateType),
            loadDayTypesForDate(vm.uiOptions[dateType + 'Date'], dateType)
          ]);
        })
        .then(function () {
          isCalculationUnit('hours') && setRequestDates();

          if (periodHasBeenChanged) {
            setMinMaxDatesToUI();
            $rootScope.$broadcast('LeaveRequestPopup::absencePeriodChanged');
          } else {
            return performBalanceChangeCalculation();
          }
        })
        .catch(handleError);
    }

    /**
     * Sets balance change breakdown after it was retrieved or calculated
     *
     * @param {Object} balanceChange
     */
    function setBalanceChange (balanceChange) {
      vm.balance.change = balanceChange;

      calculateClosingBalance();
      rePaginate();

      vm.loading.balanceChange = false;
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
     * Sets the collection for given day types to sent list of day types,
     * also initializes the day types
     *
     * @param {String} dayType like `from` or `to`
     * @param {Array} listOfDayTypes collection of available day types
     */
    function setDayTypes (dateType, listOfDayTypes) {
      // will create either of leaveRequestFromDayTypes or leaveRequestToDayTypes key
      var keyForDayTypeCollection = 'request' + _.startCase(dateType) + 'DayTypes';

      vm[keyForDayTypeCollection] = listOfDayTypes;

      if (vm.isMode('create')) {
        vm.request[dateType + '_date_type'] = vm[keyForDayTypeCollection][0].value;
      }
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
     * Sets absence period for the changed date if it exists
     *
     * @param {String} dateType from|to
     * @return {Promise} if period found, resolves with TRUE if period has changed,
     * with FALSE if period has not been changed, else rejects
     */
    function isAbsencePeriodChanged (dateType) {
      var previousAbsencePeriodId = vm.period.id;
      var formattedDate = moment(vm.uiOptions[dateType + 'Date'])
        .format(vm.uiOptions.userDateFormat.toUpperCase());

      vm.period = _.find(vm.absencePeriods, function (period) {
        return period.isInPeriod(formattedDate);
      });

      if (!vm.period) {
        vm.period = {};
        // inform user if absence period is not found
        vm.loading['fromDayTypes'] = false;

        return $q.reject('Please change date as it is not in any absence period');
      }

      return $q.resolve(previousAbsencePeriodId !== vm.period.id);
    }

    /**
     * This function sets dates to request from UI
     * In case of "hours" unit type it also sets time, which is a part of
     * the date in the request instance.
     */
    function setRequestDates () {
      var options = vm.uiOptions;
      var request = vm.request;
      var times = options.times;

      request.from_date = options.fromDate ? convertDateToServerFormat(options.fromDate) : null;
      request.to_date = options.toDate ? convertDateToServerFormat(options.toDate) : null;

      if (isCalculationUnit('hours') && !isLeaveType('toil')) {
        request.from_date = request.from_date && times.from.time ? request.from_date + ' ' + times.from.time : null;
        request.to_date = request.to_date && times.to.time ? request.to_date + ' ' + times.to.time : null;
      }

      if (!options.multipleDays && options.fromDate) {
        request.to_date = request.from_date;
        request.to_date_type = request.from_date_type;
      }
    }

    /**
     * Sets dates (with time in case of "hours" Absence Type) from UI to vm.request
     *
     * @param  {String} dateType from|to
     * @return {Promise}
     */
    function setDate (dateType) {
      setMinMaxDatesToUI();
      (dateType === 'from') && checkToDate();
      resetUIDayTypesTimeAndDeductions(dateType);

      vm.uiOptions.times[dateType].loading = true;
      vm.loading[dateType + 'DayTypes'] = true;

      return $q.resolve()
        .then(vm.onDateChange)
        .then(function () {
          return setAbsencePeriod(dateType);
        });
    }

    /**
     * Sets deductions in hours from UI to vm.request
     */
    function setRequestHoursDeductions () {
      var times = vm.uiOptions.times;

      vm.request.from_date_amount = !isNaN(+times.from.amount) ? times.from.amount : null;
      vm.request.to_date_amount = !isNaN(+times.to.amount) ? times.to.amount : null;
    }

    /**
     * Sets the min and max for to date from absence period. It also sets the
     * init/starting date which user can select from. For multiple days request
     * user can select to date which is one more than the the start date.
     */
    function setMinMaxDatesToUI () {
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
     * Reset day types, times and deductions.
     *
     * @param {String} dateType from|to
     */
    function resetUIDayTypesTimeAndDeductions (dateType) {
      var time = vm.uiOptions.times[dateType];

      vm['request' + _.startCase(dateType) + 'DayTypes'] = [];
      time.time = '';
      time.min = '00:00';
      time.max = '00:00';
      time.amount = '0';
      time.maxAmount = '0';
      time.disabled = true;

      setRequestDates();
      setRequestHoursDeductions();
      toggleBalance();
    }

    /**
     * Shows or hides the balance breakdown depending on various conditions
     */
    function toggleBalance () {
      vm.uiOptions.showBalance = vm.canCalculateChange();
    }
  }
});
