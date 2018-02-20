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
    var workDays = {};

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
    vm.isLeaveType = isLeaveType;
    vm.isNotWorkingDay = isNotWorkingDay;
    vm.performBalanceChangeCalculation = performBalanceChangeCalculation;
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
          if (!vm.isMode('create') && isCalculationUnit('hours')) {
            return initTimes();
          }
        })
        .then(!vm.isMode('create') && setDatepickerBoundariesForToDate)
        .then(initOriginalOpeningBalance)
        .then(setOpeningBalance)
        .then(initBalanceChange)
        .then(function () {
          if (!vm.isMode('view') && !isLeaveType('toil')) {
            initTimeAndDateInputsWatchers();
          }
        })
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
          resetUIDayTypesTimeAndDeductions(dateType);

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
        .then(vm.onDateChangeExtended)
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
          return loadDayTypesForDate(vm.uiOptions[dateType + 'Date'], dateType);
        })
        .then(function () {
          if (isCalculationUnit('hours')) {
            return loadTimeRangesFromWorkPattern(dateType)
              .then(function () {
                setDeductionMaximumBoundary(dateType, true);
              });
          }
        })
        .then(function () {
          setRequestDateTimesAndDateTypes();

          if (absencePeriodChanged) {
            $rootScope.$broadcast('LeaveRequestPopup::absencePeriodChanged');
          } else {
            return performBalanceChangeCalculation();
          }
        })
        .catch(handleError);
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

      resetUIDayTypesTimeAndDeductions('to');

      return $q.resolve()
        .then(function () {
          if (isCalculationUnit('hours') && vm.uiOptions.fromDate) {
            disableAndShowLoadingTimeInput('from');
            (!vm.uiOptions.multipleDays) && disableAndShowLoadingTimeInput('to');

            return loadTimeRangesFromWorkPattern('from')
              .then(function () {
                setDeductionMaximumBoundary('from', true);
              })
              .then(setRequestDateTimesAndDateTypes);
          }
        })
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
     * Enables a time input of the specified type
     * and sets provided data such as minumum and maximum time values and time
     *
     * @param {String} type from|to
     */
    function enableAndSetDataToTimeInput (type, data) {
      var timeObject = vm.uiOptions.times[type];
      var timeMin = _.clone(data.time_from);
      var timeMax = _.clone(data.time_to);

      if (!vm.uiOptions.multipleDays) {
        if (type === 'from' && timeMax && timeMin) {
          timeMax = getMomentDateWithGivenTime(timeMax)
            .subtract(vm.uiOptions.time_interval, 'minutes')
            .format('HH:mm');
        }
        if (type === 'to' && timeMin && timeMax) {
          timeMin = getMomentDateWithGivenTime(timeMin)
            .add(vm.uiOptions.time_interval, 'minutes')
            .format('HH:mm');
        }
      }

      timeObject.min = timeMin || '00:00';
      timeObject.max = timeMax || '00:00';
      timeObject.time = type === 'to' ? timeObject.max : timeObject.min;
      timeObject.disabled = false;
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
      }).then(function (nameFilter) {
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
     * Calculates time difference in hours
     *
     * @param  {String} timeFrom in HH:mm format
     * @param  {String} timeTo in HH:mm format
     * @return {String} amount of hours, eg. '7.5'
     */
    function getTimeDifferenceInHours (timeFrom, timeTo) {
      return (getMomentDateWithGivenTime(timeTo)
        .diff(getMomentDateWithGivenTime(timeFrom), 'minutes') / 60)
        .toString();
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
     * Initialises and sets the "from" and "to" times
     *
     * @return {Promise}
     */
    function initTimes () {
      var dateTypes = vm.uiOptions.multipleDays ? ['from', 'to'] : ['from'];
      var times = vm.uiOptions.times;

      return $q.all(dateTypes.map(loadTimeRangesFromWorkPattern))
        .then(function () {
          ['from', 'to'].forEach(function (dateType) {
            times[dateType].time = extractTimeFromServerDate(vm.request[dateType + '_date']);

            setDeductionMaximumBoundary(dateType);

            times[dateType].amount =
              Math.min(vm.request[dateType + '_date_amount'], times[dateType].maxAmount).toString();
          });

          if (!vm.uiOptions.multipleDays) {
            updateEndTimeInputMinTime(vm.uiOptions.times.from.time);
          }
        }).then(setRequestHoursDeductions);
    }

    /**
     * Initialises watchers for time and date inputs
     */
    function initTimeAndDateInputsWatchers () {
      ['from', 'to'].forEach(function (dateType) {
        $scope.$watch('detailsTab.uiOptions.times.' + dateType + '.amount', function (amount, oldAmount) {
          if (!isCalculationUnit('days') && +amount !== +oldAmount) {
            setRequestHoursDeductions();
            // @NOTE `vm.` is needed for testing purposes
            vm.performBalanceChangeCalculation();
          }
        });
        $scope.$watch('detailsTab.uiOptions.times.' + dateType + '.time', function (time, oldTime) {
          if (!isCalculationUnit('days') && time !== oldTime) {
            setRequestDateTimesAndDateTypes();

            if (!time) {
              return;
            }

            (!vm.uiOptions.multipleDays && dateType === 'from') && updateEndTimeInputMinTime(time);
            setDeductionMaximumBoundary(dateType, true);
          }
        });
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
     * Loads time ranges from work pattern,
     * sets time ranges for timepickers and maximum value for deduction.
     *
     * @param  {String} dateType from|to
     * @return {Promise}
     */
    function loadTimeRangesFromWorkPattern (dateType) {
      var date = vm.uiOptions[dateType + 'Date'];
      var timeObject = vm.uiOptions.times[dateType];
      var isSingleDayRequest = !vm.uiOptions.multipleDays;

      if (!date) {
        return $q.resolve();
      }

      return vm.request.getWorkDayForDate(convertDateToServerFormat(date))
        .then(function (workDay) {
          workDays[dateType] = workDay;

          enableAndSetDataToTimeInput(dateType, workDay);
          (isSingleDayRequest && dateType === 'from') && enableAndSetDataToTimeInput('to', workDay);
        })
        .catch(function (err) {
          workDays[dateType] = {};

          return handleError(err);
        })
        .finally(function () {
          timeObject.loading = false;

          isSingleDayRequest && (vm.uiOptions.times['to'].loading = false);
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
     * Checks To Date and flushes it if it is the same or before
     * the "from date" in case of the multiple day request.
     */
    function resetToDateIfGreaterThanFromDate () {
      if (
        vm.uiOptions.toDate && vm.uiOptions.fromDate && vm.uiOptions.multipleDays &&
        moment(vm.uiOptions.toDate).isSameOrBefore(vm.uiOptions.fromDate)
      ) {
        vm.uiOptions.toDate = null;

        resetUIDayTypesTimeAndDeductions('to');
      }
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
      time.loading = false;

      setRequestDateTimesAndDateTypes();
      setRequestHoursDeductions();
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
     * Sets the collection for given day types to sent list of day types,
     * also initializes the day types
     *
     * @param {String} dateType from|to
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
     * Sets deduction maximum and default amounts for a given day type
     *
     * @param {String} timeType from|to
     * @param {Boolean} setDefaultValue if TRUE, then set the current value to maximum
     */
    function setDeductionMaximumBoundary (timeType, setDefaultValue) {
      var uiOptions = vm.uiOptions;
      var dateType = uiOptions.multipleDays ? timeType : 'from';
      var timeObject = uiOptions.times[dateType];
      var timeFrom = uiOptions.multipleDays && dateType === 'to' ? timeObject.min : uiOptions.times.from.time;
      var timeTo = uiOptions.multipleDays && dateType === 'from' ? timeObject.max : uiOptions.times.to.time;
      var deduction = workDays[dateType].number_of_hours ? getTimeDifferenceInHours(timeFrom, timeTo) : '0';

      timeObject.maxAmount = deduction;
      (setDefaultValue) && (timeObject.amount = timeObject.maxAmount);
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

      if (isCalculationUnit('hours') && !isLeaveType('toil')) {
        vm.request.from_date = vm.request.from_date && times.from.time ? vm.request.from_date + ' ' + times.from.time : null;
        vm.request.to_date = vm.request.to_date && times.to.time ? vm.request.to_date + ' ' + times.to.time : null;
      }
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
          if (absenceTypeUnitChanged && isCalculationUnit('hours')) {
            return loadTimeRangesFromWorkPattern('from')
              .then(function () {
                setDeductionMaximumBoundary('from', true);
              });
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

        resetUIDayTypesTimeAndDeductions('to');
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
