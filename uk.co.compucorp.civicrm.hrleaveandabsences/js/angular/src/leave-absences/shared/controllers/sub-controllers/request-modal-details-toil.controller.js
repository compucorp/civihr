/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers'
], function (_, controllers) {
  controllers.controller('RequestModalDetailsToilController', RequestModalDetailsToilController);

  RequestModalDetailsToilController.$inject = ['$log', '$q', '$rootScope', 'api.optionGroup', 'AbsenceType', 'detailsController'];

  function RequestModalDetailsToilController ($log, $q, $rootScope, OptionGroup, AbsenceType, detailsController) {
    $log.debug('RequestModalDetailsToilController');

    detailsController.requestCanExpire = true;

    detailsController.calculateBalanceChange = calculateBalanceChange;
    detailsController.canCalculateChange = canCalculateChange;
    detailsController.checkSubmitConditions = checkSubmitConditions;
    detailsController.clearExpiryDate = clearExpiryDate;
    detailsController.initChildController = initChildController;
    detailsController.onDateChangeExtended = onDateChangeExtended;
    detailsController.setDaysSelectionModeExtended = setDaysSelectionModeExtended;
    detailsController.updateExpiryDate = updateExpiryDate;

    (function init () {
      initAccrueValueWatcher();
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
     * Calculates toil expiry date.
     *
     * @return {Promise}
     */
    function calculateToilExpiryDate () {
      // skips calculation of expiration date if request never expires
      // according to admin setting
      if (!detailsController.requestCanExpire) {
        detailsController.request.toil_expiry_date = false;

        return $q.resolve(false);
      }

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
     * Determines if the expiry date can be calculated based on the
     * Number Of Days selected and the corresponding date field has value.
     *
     * @return {Boolean}
     */
    function canCalculateExpiryDate () {
      var requestExistsAndCantBeManaged = (!detailsController.canManage && detailsController.request.id);
      var multipleDaysWithToDateSet = (detailsController.uiOptions.multipleDays && detailsController.request.to_date);
      var singleDayWithFromDateSet = (!detailsController.uiOptions.multipleDays && detailsController.request.from_date);

      return requestExistsAndCantBeManaged && (multipleDaysWithToDateSet || singleDayWithFromDateSet);
    }

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function checkSubmitConditions () {
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
        return $q.resolve(field.value);
      }
    }

    /**
     * Initialize the controller
     *
     * @return {Promise}
     */
    function initChildController () {
      detailsController.request.to_date_type = detailsController.request.from_date_type = '1';

      return initRequestCanExpire()
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
      if (detailsController.isMode('view')) {
        return;
      }

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
     * Initialize requestCanExpire according to admin setting
     * and request type.
     * @return {Promise}
     */
    function initRequestCanExpire () {
      return AbsenceType.canExpire(detailsController.request.type_id)
      .then(function (canExpire) {
        detailsController.requestCanExpire = canExpire;
      });
    }

    /**
     * Initializes leave request toil amounts
     *
     * @return {Promise}
     */
    function loadToilAmounts () {
      return OptionGroup.valuesOf('hrleaveandabsences_toil_amounts')
        .then(function (amounts) {
          detailsController.toilAmounts = _.indexBy(amounts, 'value');
        });
    }

    /**
     * Extends the parent's dateChangeHandler() function
     *
     * @return {Promise}
     */
    function onDateChangeExtended () {
      return calculateToilExpiryDate().catch($q.resolve);
    }

    /**
     * Extends setDaysSelectionMode() method from the details controller.
     * Fires calculation of expiry date when the number of days changes
     * and the expiry date can be calculated.
     *
     * @return {Promise}
     */
    function setDaysSelectionModeExtended () {
      return canCalculateExpiryDate() ? calculateToilExpiryDate() : $q.resolve();
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
