/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('RequestModalDetailsLeaveController', RequestModalDetailsLeaveController);

  RequestModalDetailsLeaveController.$inject = ['$controller', '$log', '$q', '$rootScope', 'detailsController', 'PublicHoliday'];

  function RequestModalDetailsLeaveController ($controller, $log, $q, $rootScope, detailsController, PublicHoliday) {
    var workDays = {};

    $log.debug('RequestModalDetailsLeaveController');

    detailsController.calculateBalanceChange = calculateBalanceChange;
    detailsController.canCalculateChange = canCalculateChange;
    detailsController.canSubmit = canSubmit;
    detailsController.initChildController = initChildController;
    detailsController.initDayTypesExtended = initDayTypes;
    detailsController.initTimesExtended = initTimes;
    detailsController.initWatchersExtended = initWatchers;
    detailsController.onAbsenceTypeUpdateExtended = updateFromTimeRangeAndDeductionBoundary;
    detailsController.onDateChangeExtended = loadDayTypesTimeRangesAndSetDeductionBoundaries;
    detailsController.resetUIInputsExtended = resetUIInputsExtended;
    detailsController.setDaysSelectionModeExtended = setDaysSelectionModeExtended;

    /**
     * Calculates balance change by fetching the balance breakdown via the API
     *
     * @return {Promise}
     */
    function calculateBalanceChange () {
      return detailsController.request.calculateBalanceChange(detailsController.selectedAbsenceType.calculation_unit_name);
    }

    /**
     * Checks if the balance change can be calculated.
     * Any request of "leave" type requires dates.
     * Requests in "days" also require date types.
     * Requests in "hours" also require deductions
     *
     * @return {Boolean}
     */
    function canCalculateChange () {
      var request = detailsController.request;
      var canCalculate = !!request.from_date && !!request.to_date;
      var unit = detailsController.selectedAbsenceType.calculation_unit_name;

      if (unit === 'days') {
        canCalculate = canCalculate &&
          !!request.from_date_type && !!request.to_date_type;
      } else if (unit === 'hours') {
        canCalculate = canCalculate &&
          !isNaN(+request.from_date_amount) && !isNaN(+request.to_date_amount);
      }

      return canCalculate;
    }

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function canSubmit () {
      return detailsController.canCalculateChange();
    }

    /**
     * Enables a time input of the specified type
     * and sets provided data such as minumum and maximum time values and time
     *
     * @param {String} type from|to
     */
    function enableAndSetDataToTimeInput (type, data) {
      var timeObject = detailsController.uiOptions.times[type];
      var timeMin = _.clone(data.time_from);
      var timeMax = _.clone(data.time_to);

      if (!detailsController.uiOptions.multipleDays) {
        if (type === 'from' && timeMax && timeMin) {
          timeMax = detailsController.getMomentDateWithGivenTime(timeMax)
            .subtract(detailsController.uiOptions.time_interval, 'minutes')
            .format('HH:mm');
        }
        if (type === 'to' && timeMin && timeMax) {
          timeMin = detailsController.getMomentDateWithGivenTime(timeMin)
            .add(detailsController.uiOptions.time_interval, 'minutes')
            .format('HH:mm');
        }
      }

      timeObject.min = timeMin || '00:00';
      timeObject.max = timeMax || '00:00';
      timeObject.time = type === 'to' ? timeObject.max : timeObject.min;
      timeObject.disabled = false;
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

      date = detailsController.convertDateToServerFormat(date);

      return PublicHoliday.isPublicHoliday(date)
        .then(function (result) {
          if (result) {
            return detailsController.requestDayTypes.filter(function (publicHoliday) {
              return publicHoliday.name === 'public_holiday';
            });
          }

          return getDayTypesFromDate(date, detailsController.requestDayTypes)
            .then(function (inCalendarList) {
              return inCalendarList.length
                ? inCalendarList
                : detailsController.requestDayTypes.filter(function (dayType) {
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
        detailsController.calendar.isNonWorkingDay(date),
        detailsController.calendar.isWeekend(date)
      ]).then(function (results) {
        return results[0] ? 'non_working_day' : (results[1] ? 'weekend' : null);
      }).then(function (nameFilter) {
        return !nameFilter ? [] : listOfDayTypes.filter(function (day) {
          return day.name === nameFilter;
        });
      });
    }

    /**
     * Calculates time difference in hours
     *
     * @param  {String} timeFrom in HH:mm format
     * @param  {String} timeTo in HH:mm format
     * @return {Number} amount of hours, eg. 7.5
     */
    function getTimeDifferenceInHours (timeFrom, timeTo) {
      return moment.duration(timeTo)
        .subtract(moment.duration(timeFrom)).asHours();
    }

    /**
     * Initialize the controller
     *
     * @return {Promise}
     */
    function initChildController () {
      return $q.resolve();
    }

    function initDayTypes () {
      return loadDayTypesForDate(detailsController.uiOptions.fromDate, 'from')
        .then(function () {
          return loadDayTypesForDate(detailsController.uiOptions.toDate, 'to');
        });
    }

    /**
     * Initialises a watcher for a custom deduction input of a specified date type
     *
     * @param {String} dateType from|to
     */
    function initDeductionInputWatcher (dateType) {
      $rootScope.$watch(function () {
        return detailsController.uiOptions.times[dateType].amount;
      }, function (amount, oldAmount) {
        if (detailsController.isCalculationUnit('days') || +amount === +oldAmount) {
          return;
        }

        if (detailsController.isRole('staff')) {
          detailsController.request.change_balance = true;
        }

        setRequestHoursDeductions();
        // @NOTE `detailsController.` is needed for testing purposes
        detailsController.performBalanceChangeCalculation();
      });
    }

    /**
     * Initialises time for a given date type.
     * In general cases simply extracts the time from the date string and
     * sets the time to the correspondent times property.
     * If the time is outside the allowed range (for example after work parrern change),
     * then it sets the minimum allowed time for "from" time
     * and the maximum allowed time for "to" time.
     *
     * @param {String} dateType from|to
     */
    function initTime (dateType) {
      var time = moment(detailsController.request[dateType + '_date']).format('HH:mm');
      var timeObject = detailsController.uiOptions.times[dateType];
      var isOutsideWorkPatternRange =
        getTimeDifferenceInHours(timeObject.min, time) <= 0 ||
        getTimeDifferenceInHours(timeObject.max, time) >= 0;

      if (isOutsideWorkPatternRange) {
        time = dateType === 'from' ? timeObject.min : timeObject.max;
      }

      detailsController.uiOptions.times[dateType].time = time;
    }

    /**
     * Initialises and sets the "from" and "to" times
     *
     * @return {Promise}
     */
    function initTimes () {
      var dateTypes = detailsController.uiOptions.multipleDays ? ['from', 'to'] : ['from'];
      var times = detailsController.uiOptions.times;

      if (!detailsController.isCalculationUnit('hours')) {
        return;
      }

      return $q.all(dateTypes.map(loadTimeRangesFromWorkPattern))
        .then(function () {
          ['from', 'to'].forEach(function (dateType) {
            initTime(dateType);

            setDeductionMaximumBoundary(dateType);

            times[dateType].amount =
              Math.min(detailsController.request[dateType + '_date_amount'], times[dateType].maxAmount).toString();
          });

          if (!detailsController.uiOptions.multipleDays) {
            detailsController.updateEndTimeInputMinTime(detailsController.uiOptions.times.from.time);
          }
        }).then(setRequestHoursDeductions);
    }

    /**
     * Initialises watchers for time and deductions inputs
     */
    function initWatchers () {
      ['from', 'to'].forEach(function (dateType) {
        initDeductionInputWatcher(dateType);
        initTimeInputWatcher(dateType);
      });
    }

    /**
     * Initialises a watcher for a time input of a specified date type
     *
     * @param {String} dateType from|to
     */
    function initTimeInputWatcher (dateType) {
      $rootScope.$watch(function () {
        return detailsController.uiOptions.times[dateType].time;
      }, function (time, oldTime) {
        if (detailsController.isCalculationUnit('days') || time === oldTime) {
          return;
        }

        detailsController.setRequestDateTimesAndDateTypes();

        if (!time) {
          return;
        }

        setDeductionMaximumBoundary(dateType, true);
      });
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
          detailsController.loading[dateType + 'DayTypes'] = false;
        });
    }

    function loadDayTypesTimeRangesAndSetDeductionBoundaries (dateType) {
      return loadDayTypesForDate(detailsController.uiOptions[dateType + 'Date'], dateType)
        .then(function () {
          if (detailsController.isCalculationUnit('hours')) {
            return loadTimeRangesFromWorkPattern(dateType)
              .then(function () {
                setDeductionMaximumBoundary(dateType, true);
              });
          }
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
      var date = detailsController.uiOptions[dateType + 'Date'];
      var isSingleDayRequest = !detailsController.uiOptions.multipleDays;

      if (!date) {
        return $q.resolve();
      }

      return detailsController.request.getWorkDayForDate(detailsController.convertDateToServerFormat(date))
        .then(function (workDay) {
          workDays[dateType] = workDay;

          enableAndSetDataToTimeInput(dateType, workDay);
          (isSingleDayRequest && dateType === 'from') && enableAndSetDataToTimeInput('to', workDay);
        })
        .catch(function (err) {
          workDays[dateType] = {};

          return detailsController.handleError(err);
        })
        .finally(function () {
          isSingleDayRequest && (detailsController.uiOptions.times['to'].loading = false);
        });
    }

    function resetUIInputsExtended (dateType) {
      var timeObject = detailsController.uiOptions.times[dateType];

      timeObject.time = '';
      timeObject.min = '00:00';
      timeObject.max = '00:00';
      timeObject.amount = '0';
      timeObject.maxAmount = '0';

      setRequestHoursDeductions();
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

      detailsController[keyForDayTypeCollection] = listOfDayTypes;

      if (detailsController.isMode('create')) {
        detailsController.request[dateType + '_date_type'] = detailsController[keyForDayTypeCollection][0].value;
      }
    }

    /**
     * Updates time ranges as per the work pattern for "from" date
     * and updates the deduction fields boundaries
     *
     * @NOTE In case of a single day, also show "to" time loading
     *
     * @return {Promise}
     */
    function setDaysSelectionModeExtended () {
      if (!detailsController.isCalculationUnit('hours') || !detailsController.uiOptions.fromDate) {
        return $q.resolve();
      }

      detailsController.disableAndShowLoadingTimeInput('from');
      (!detailsController.uiOptions.multipleDays) && detailsController.disableAndShowLoadingTimeInput('to');

      return loadTimeRangesFromWorkPattern('from')
        .then(function () {
          setDeductionMaximumBoundary('from', true);
        });
    }

    /**
     * Sets deduction maximum and default amounts for a given day type
     *
     * @param {String} timeType from|to
     * @param {Boolean} setDefaultValue if TRUE, then set the current value to maximum
     */
    function setDeductionMaximumBoundary (timeType, setDefaultValue) {
      var uiOptions = detailsController.uiOptions;
      var dateType = uiOptions.multipleDays ? timeType : 'from';
      var timeObject = uiOptions.times[dateType];
      var timeFrom = uiOptions.multipleDays && dateType === 'to' ? timeObject.min : uiOptions.times.from.time;
      var timeTo = uiOptions.multipleDays && dateType === 'from' ? timeObject.max : uiOptions.times.to.time;
      var deduction = workDays[dateType].number_of_hours
        ? getTimeDifferenceInHours(timeFrom, timeTo).toString()
        : '0';

      timeObject.maxAmount = deduction;
      (setDefaultValue) && (timeObject.amount = timeObject.maxAmount);
    }

    /**
     * Sets deductions in hours from UI to detailsController.request
     */
    function setRequestHoursDeductions () {
      var times = detailsController.uiOptions.times;

      detailsController.request.from_date_amount = !isNaN(+times.from.amount) ? times.from.amount : null;
      detailsController.request.to_date_amount = !isNaN(+times.to.amount) ? times.to.amount : null;
    }

    /**
     * Updates time ranges as per the work pattern for "from" date
     * and updates the deduction fields boundaries
     *
     * @return {Promise}
     */
    function updateFromTimeRangeAndDeductionBoundary () {
      if (!detailsController.isCalculationUnit('hours')) {
        return;
      }

      return loadTimeRangesFromWorkPattern('from')
        .then(function () {
          setDeductionMaximumBoundary('from', true);
        });
    }
  }
});
