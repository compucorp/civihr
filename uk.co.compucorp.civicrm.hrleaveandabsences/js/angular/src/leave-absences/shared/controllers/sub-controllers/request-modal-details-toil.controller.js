/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers'
], function (_, controllers) {
  controllers.controller('RequestModalDetailsToilController', RequestModalDetailsToilController);

  RequestModalDetailsToilController.$inject = ['$log', '$q', '$rootScope', 'api.optionGroup', 'AbsenceType', 'detailsController'];

  function RequestModalDetailsToilController ($log, $q, $rootScope, OptionGroup, AbsenceType, detailsController) {
    $log.debug('RequestModalDetailsToilController');

    var initialRequestAttributes;
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
    detailsController.onDateChangeExtended = tryToCalculateExpiryDate;
    detailsController.setDaysSelectionModeExtended = tryToCalculateExpiryDate;
    detailsController.updateExpiryDate = updateExpiryDate;

    (function init () {
      initAccrueValueWatcher();
      setInitialRequestAttributes();
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
        return $q.resolve(field.value);
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
     * Stores the initial request attributes to determine if there has been a change.
     */
    function setInitialRequestAttributes () {
      initialRequestAttributes = angular.copy(detailsController.request.attributes());
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
