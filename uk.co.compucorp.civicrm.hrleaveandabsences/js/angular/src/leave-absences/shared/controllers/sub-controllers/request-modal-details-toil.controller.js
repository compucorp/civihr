/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('RequestModalDetailsToilController', RequestModalDetailsToilController);

  RequestModalDetailsToilController.$inject = ['$log', '$q', '$rootScope',
    'crmAngService', 'OptionGroup', 'AbsenceType', 'detailsController'];

  function RequestModalDetailsToilController ($log, $q, $rootScope,
    crmAngService, OptionGroup, AbsenceType, detailsController) {
    $log.debug('RequestModalDetailsToilController');

    var initialRequestAttributes;
    var skipSettingDefaultDuration = !detailsController.isMode('create');
    var expirationConditions = {
      hasPreviousExpirationDate: null,
      hasExpirationFromAdminSettings: null
    };

    detailsController.canDisplayToilExpirationField = false;
    detailsController.calculateBalanceChange = calculateBalanceChange;
    detailsController.canCalculateChange = canCalculateChange;
    detailsController.canSubmit = canSubmit;
    detailsController.clearExpiryDate = clearExpiryDate;
    detailsController.initChildController = initChildController;
    detailsController.initTimesExtended = initTimes;
    detailsController.initWatchersExtended = initWatchers;
    detailsController.onDateChangeExtended = onDateChangeHandler;
    detailsController.openToilInDaysAccrualOptionsEditor = openToilInDaysAccrualOptionsEditor;
    detailsController.setDaysSelectionModeExtended = onDaysSelectionModeHandler;
    detailsController.updateExpiryDate = updateExpiryDate;

    (function init () {
      setInitialRequestAttributes();
      setTimeInputsRanges();
      toggleAccrualOptionsGroupEditorIcon();
      !detailsController.isMode('create') && initDuration();
    })();

    /**
     * Calculates change in balance
     * (overrides the parent's implementation)
     *
     * @return {Promise} resolves with the balance change
     */
    function calculateBalanceChange () {
      detailsController.balance.change.amount = +detailsController.request.toil_to_accrue;

      return $q.resolve(detailsController.balance.change);
    }

    /**
     * Calculates the TOIL maximum duration and sets its default value
     */
    function calculateDuration () {
      if (!detailsController.request.from_date || !detailsController.request.to_date) {
        detailsController.uiOptions.max_toil_duration_and_accrual = null;
        detailsController.uiOptions.toil_duration_in_hours = null;

        return;
      }

      detailsController.uiOptions.max_toil_duration_and_accrual =
        moment.duration(moment(detailsController.request.to_date).diff(detailsController.request.from_date)).asHours();
    }

    /**
     * Calculates the TOIL expiry date and updates the UI and the Request's
     * expiry date value.
     *
     * @return {Promise}
     */
    function calculateToilExpiryDate () {
      return getReferenceDate()
        .catch(function (errors) {
          if (errors.length) {
            detailsController.errors = errors;
          }

          return $q.reject(errors);
        }).then(function (referenceDate) {
          return AbsenceType.calculateToilExpiryDate(
            detailsController.request.type_id,
            referenceDate
          );
        })
        .then(function (expiryDate) {
          detailsController.request.toil_expiry_date = expiryDate;
          detailsController.uiOptions.expiryDate = new Date(expiryDate);

          return expiryDate;
        });
    }

    /**
     * Checks if change can be calculated
     *
     * @return {Boolean}
     */
    function canCalculateChange () {
      return !!detailsController.request.toil_to_accrue;
    }

    /**
     * Determines if the expiry date can be calculated based on the visibility
     * of the expiry date field, if the dates selected are valid, TOIL requests
     * are set to expire according to the admin settings, and the selected
     * dates have changed.
     *
     * @return {Boolean}
     */
    function canCalculateExpiryDate () {
      var multipleDaysWithToDateSet = (detailsController.uiOptions.multipleDays &&
        !!detailsController.request.to_date);
      var singleDayWithFromDateSet = (!detailsController.uiOptions.multipleDays &&
        !!detailsController.request.from_date);
      var dateFieldsAreDefined = singleDayWithFromDateSet || multipleDaysWithToDateSet;
      var datesHaveChanged = initialRequestAttributes.from_date !== detailsController.request.from_date ||
        initialRequestAttributes.to_date !== detailsController.request.to_date;

      return detailsController.canDisplayToilExpirationField && dateFieldsAreDefined &&
        expirationConditions.hasExpirationFromAdminSettings &&
        datesHaveChanged;
    }

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function canSubmit () {
      return !!detailsController.request.from_date && !!detailsController.request.to_date &&
        !!detailsController.request.toil_duration && !!detailsController.request.toil_to_accrue;
    }

    /**
     * Clears the request's expiry date and the UI expiry date picker.
     */
    function clearExpiryDate () {
      detailsController.request.toil_expiry_date = false;
      detailsController.uiOptions.expiryDate = null;
    }

    /**
     * Returns a promise with a date that can be used to calculate the expiry
     * date. This date depends on the Multiple Days or Single Day options.
     *
     * @return {Promise}
     */
    function getReferenceDate () {
      var isMultipleDays = detailsController.uiOptions.multipleDays;
      var request = detailsController.request;

      return getReferenceDateForField({
        hasErrors: isMultipleDays
          ? !request.to_date && !request.from_date
          : !request.from_date,
        label: isMultipleDays ? 'To Date' : 'From Date',
        value: isMultipleDays ? request.to_date : request.to_date
      });
    }

    /**
     * Returns a reference date using the field object as source.
     * If the field has errors, it returns an error message.
     * If the field has no value, it returns an empty message since it still
     * is in the process of inserting values.
     * And if everything is ok it returns the field's date value.
     *
     * @return {Promise}
     */
    function getReferenceDateForField (field) {
      if (field.hasErrors) {
        var message = 'Please select ' + field.label + ' to find expiry date';
        return $q.reject([message]);
      }

      if (!field.value) {
        return $q.reject([]);
      } else {
        return $q.resolve(moment(field.value).format('YYYY-MM-DD'));
      }
    }

    /**
     * Initializes the *canDisplayToilExpirationField* property which can be used
     * to display or hide the expiration date field for TOIL requests.
     */
    function initCanDisplayToilExpirationField () {
      var isNewRequestAndRequestsCanExpire = detailsController.isMode('create') &&
        expirationConditions.hasExpirationFromAdminSettings;
      var isOldRequestAndHasExpiryDateDefined = expirationConditions.hasPreviousExpirationDate;
      var isToilRequest = detailsController.isLeaveType('toil');
      var userCanManageRequest = detailsController.canManage;

      detailsController.canDisplayToilExpirationField = isToilRequest && (
        userCanManageRequest ||
        isNewRequestAndRequestsCanExpire ||
        isOldRequestAndHasExpiryDateDefined
      );
    }

    /**
     * Initialises duration by converting it from minutes to hours
     * and setting to a separate variable in UI options
     */
    function initDuration () {
      detailsController.uiOptions.toil_duration_in_hours =
        detailsController.request.toil_duration / 60;
    }

    /**
     * Initialises the expiration conditions for the current toil request
     * using the following rules:
     * - hasPreviousExpirationDate: set to true if the request is in edit mode
     * and the expiration date was previously set.
     * - hasExpirationFromAdminSettings: set to true if TOIL requests are set to
     * never expire in the admin settings.
     *
     * @return {Promise}
     */
    function determineExpirationConditions () {
      expirationConditions.hasPreviousExpirationDate = detailsController.isMode('edit') &&
        !!detailsController.request.toil_expiry_date;

      return AbsenceType.canExpire(detailsController.request.type_id)
        .then(function (canExpire) {
          expirationConditions.hasExpirationFromAdminSettings = canExpire;
        });
    }

    /**
     * Initialize the controller
     *
     * @return {Promise}
     */
    function initChildController () {
      detailsController.request.to_date_type = detailsController.request.from_date_type = '1';

      return determineExpirationConditions()
        .then(initCanDisplayToilExpirationField)
        .then(initExpiryDate)
        .then(loadToilAmounts);
    }

    /**
     * Initialize expiryDate on UI from server's toil_expiry_date
     */
    function initExpiryDate () {
      if (detailsController.canManage) {
        detailsController.uiOptions.expiryDate = detailsController.convertDateFormatFromServer(detailsController.request.toil_expiry_date);
      }
    }

    /**
     * Initialises watcher for accrue value.
     * When accrue value changes it, if possible, calculates the balance change.
     */
    function initAccrueValueWatcher () {
      $rootScope.$watch(
        function () {
          return detailsController.request.toil_to_accrue;
        },
        function (oldValue, newValue) {
          if (+oldValue !== +newValue) {
            detailsController.performBalanceChangeCalculation();
          }
        });
    }

    /**
     * Initialises watcher for Duration value.
     * When Duration value changes, it sets this value to the Accrual as well.
     */
    function initDurationValueWatcher () {
      $rootScope.$watch(
        function () {
          return detailsController.uiOptions.toil_duration_in_hours;
        },
        function (oldValue, newValue) {
          if (detailsController.isCalculationUnit('hours') && oldValue !== newValue) {
            detailsController.request.toil_to_accrue =
              detailsController.uiOptions.toil_duration_in_hours;
          }

          detailsController.request.toil_duration =
            detailsController.uiOptions.toil_duration_in_hours
              ? detailsController.uiOptions.toil_duration_in_hours * 60
              : null;
        });
    }

    /**
     * Initialises and sets the "from" and "to" times
     *
     * @return {Promise}
     */
    function initTimes () {
      var times = detailsController.uiOptions.times;

      times.from.time = moment(detailsController.request.from_date).format('HH:mm');
      times.to.time = moment(detailsController.request.to_date).format('HH:mm');

      if (!detailsController.uiOptions.multipleDays) {
        detailsController.updateEndTimeInputMinTime(detailsController.uiOptions.times.from.time);
      }
    }

    /**
     * Initialises watcher for times values.
     * The values of time fields define the maximum and default Duration values.
     */
    function initTimesWatcher () {
      ['from', 'to'].forEach(function (dateType) {
        $rootScope.$watch(
          function () {
            return detailsController.uiOptions.times[dateType].time;
          },
          function (oldValue, newValue) {
            if (oldValue === newValue) {
              return;
            }

            detailsController.setRequestDateTimesAndDateTypes();
            tryToCalculateExpiryDate();
            calculateDuration();
            setDefaultDuration();
          });
      });
    }

    /**
     * Initialises watchers for Accruals and Duration values
     */
    function initWatchers () {
      if (detailsController.isMode('view')) {
        return;
      }

      initAccrueValueWatcher();
      initDurationValueWatcher();
      initTimesWatcher();
    }

    /**
     * Initializes leave request toil amounts
     *
     * @param  {Boolean} cache if to cache results of the API call, cache by default
     * @return {Promise}
     */
    function loadToilAmounts (cache) {
      return OptionGroup.valuesOf('hrleaveandabsences_toil_amounts', cache)
        .then(function (amounts) {
          detailsController.toilAmounts = _.sortBy(amounts, 'value');
        });
    }

    /**
     * Handles the dates change
     */
    function onDateChangeHandler () {
      calculateDuration();
      setDefaultDuration();

      return tryToCalculateExpiryDate();
    }

    /**
     * Handles the days selection mode change
     */
    function onDaysSelectionModeHandler () {
      setTimeInputsRanges();

      if (!detailsController.uiOptions.multipleDays) {
        detailsController.updateEndTimeInputMinTime(detailsController.uiOptions.times.from.time);
      }

      calculateDuration();
      !skipSettingDefaultDuration ? setDefaultDuration() : (skipSettingDefaultDuration = false);

      return tryToCalculateExpiryDate();
    }

    /**
     * Sets default duration as a maximum allowed duration value
     */
    function setDefaultDuration () {
      detailsController.uiOptions.toil_duration_in_hours =
        detailsController.uiOptions.max_toil_duration_and_accrual;
    }

    /**
     * Stores the initial request attributes to determine if there has been a change.
     */
    function setInitialRequestAttributes () {
      initialRequestAttributes = _.cloneDeep(detailsController.request.attributes());
    }

    /**
     * Sets ranges for both start and end times depending on the day selection mode
     */
    function setTimeInputsRanges () {
      if (detailsController.uiOptions.multipleDays) {
        ['from', 'to'].forEach(function (dateType) {
          detailsController.uiOptions.times[dateType].min = '00:00';
          detailsController.uiOptions.times[dateType].max = '23:45';
        });
      } else {
        detailsController.uiOptions.times.from.min = '00:00';
        detailsController.uiOptions.times.from.max = '23:30';
        detailsController.uiOptions.times.to.min = '00:15';
        detailsController.uiOptions.times.to.max = '23:45';
      }
    }

    /**
     * Opens the CRM modal that allows to edit TOIL in days amounts options
     * and reloads these options in the Leave Request Modal
     * if they are changed via the CRM modal
     */
    function openToilInDaysAccrualOptionsEditor () {
      crmAngService.loadForm('/civicrm/admin/options/hrleaveandabsences_toil_amounts?reset=1')
        .on('crmFormSuccess', function () {
          loadToilAmounts(false);
        });
    }

    /**
     * Toggles the TOIL accrual options group editor icon
     * depending on the site section the Leave Request Modal is opened at
     */
    function toggleAccrualOptionsGroupEditorIcon () {
      detailsController.showTOILAccrualsOptionEditorIcon =
        _.includes(['admin-dashboard', 'absence-tab'], $rootScope.section);
    }

    /**
     * Calculates the expiry date if all conditions are met, otherwise it resolves
     * into an empty promise.
     *
     * @return {Promise}
     */
    function tryToCalculateExpiryDate () {
      return canCalculateExpiryDate()
        ? calculateToilExpiryDate().catch($q.resolve)
        : $q.resolve();
    }

    /**
     * Updates expiry date when user changes it on ui
     */
    function updateExpiryDate () {
      if (detailsController.uiOptions.expiryDate) {
        detailsController.request.toil_expiry_date = detailsController.convertDateToServerFormat(detailsController.uiOptions.expiryDate);
      }
    }
  }
});
