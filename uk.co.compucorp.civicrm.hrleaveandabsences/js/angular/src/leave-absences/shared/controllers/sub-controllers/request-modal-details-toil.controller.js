/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers'
], function (_, controllers) {
  controllers.controller('RequestModalDetailsToilController', RequestModalDetailsToilController);

  RequestModalDetailsToilController.$inject = ['$log', '$q', '$rootScope', 'api.optionGroup', 'AbsenceType', 'parentCtrl'];

  function RequestModalDetailsToilController ($log, $q, $rootScope, OptionGroup, AbsenceType, parentCtrl) {
    $log.debug('RequestModalDetailsToilController');

    parentCtrl.requestCanExpire = true;

    parentCtrl.calculateBalanceChange = calculateBalanceChange;
    parentCtrl.calculateToilExpiryDate = calculateToilExpiryDate;
    parentCtrl.canCalculateChange = canCalculateChange;
    parentCtrl.changeInNoOfDaysExtended = changeInNoOfDaysExtended;
    parentCtrl.checkSubmitConditions = checkSubmitConditions;
    parentCtrl.clearExpiryDate = clearExpiryDate;
    parentCtrl.initChildController = initChildController;
    parentCtrl.setDatesFromUIExtended = setDatesFromUIExtended;
    parentCtrl.updateExpiryDate = updateExpiryDate;

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
      parentCtrl.balance.change.amount = +parentCtrl.request.toil_to_accrue;

      return $q.resolve(parentCtrl.balance.change);
    }

    /**
     * Calculates toil expiry date.
     *
     * @return {Promise}
     */
    function calculateToilExpiryDate () {
      // blocks the expiry date from updating if this is an existing request
      // and user is not a manager or admin
      if (!parentCtrl.canManage && parentCtrl.request.id) {
        return $q.resolve(parentCtrl.request.toil_expiry_date);
      }

      // skips calculation of expiration date if request never expires
      // according to admin setting
      if (!parentCtrl.requestCanExpire) {
        parentCtrl.request.toil_expiry_date = false;
        return $q.resolve(false);
      }

      return getReferenceDate().catch(function (errors) {
        if (errors.length) parentCtrl.errors = errors;
        return $q.reject(errors);
      }).then(function (referenceDate) {
        return AbsenceType.calculateToilExpiryDate(
          parentCtrl.request.type_id,
          referenceDate
        );
      })
      .then(function (expiryDate) {
        parentCtrl.request.toil_expiry_date = expiryDate;
        parentCtrl.uiOptions.expiryDate = new Date(expiryDate);

        return expiryDate;
      });
    }

    /**
     * Checks if change can be calculated
     *
     * @return {Boolean}
     */
    function canCalculateChange () {
      return !!parentCtrl.request.toil_to_accrue;
    }

    /**
     * Determines if the expiry date can be calculated based on the
     * Number Of Days selected and the corresponding date field has value.
     *
     * @return {Boolean}
     */
    function canCalculateExpiryDate () {
      return (parentCtrl.uiOptions.multipleDays && parentCtrl.request.to_date) ||
        (!parentCtrl.uiOptions.multipleDays && parentCtrl.request.from_date);
    }

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function checkSubmitConditions () {
      return !!parentCtrl.request.from_date && !!parentCtrl.request.to_date &&
        !!parentCtrl.request.toil_duration && !!parentCtrl.request.toil_to_accrue;
    }

    /**
     * Extends parent method. Fires calculation of expiry date when the
     * number of days changes and the expiry date can be calculated.
     *
     * @return {Promise}
     */
    function changeInNoOfDaysExtended () {
      return canCalculateExpiryDate() ? calculateToilExpiryDate() : _.noop;
    }

    /**
     * Clears the request's expiry date and the UI expiry date picker.
     */
    function clearExpiryDate () {
      parentCtrl.request.toil_expiry_date = false;
      parentCtrl.uiOptions.expiryDate = null;
    }

    /**
     * Returns a promise with a date that can be used to calculate the expiry
     * date. This date depends on the Multiple Days or Single Day options.
     *
     * @return {Promise}
     */
    function getReferenceDate () {
      if (parentCtrl.uiOptions.multipleDays) {
        return getReferenceDateForField({
          hasErrors: !parentCtrl.request.to_date && !parentCtrl.request.from_date,
          label: 'To Date',
          value: parentCtrl.request.to_date
        });
      } else {
        return getReferenceDateForField({
          hasErrors: !parentCtrl.request.from_date,
          label: 'From Date',
          value: parentCtrl.request.from_date
        });
      }
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
      parentCtrl.request.to_date_type = parentCtrl.request.from_date_type = '1';

      return initRequestCanExpire()
        .then(function () {
          initExpiryDate();

          return loadToilAmounts();
        });
    }

    /**
     * Initialize expiryDate on UI from server's toil_expiry_date
     */
    function initExpiryDate () {
      if (parentCtrl.canManage) {
        parentCtrl.uiOptions.expiryDate = parentCtrl.convertDateFormatFromServer(parentCtrl.request.toil_expiry_date);
      }
    }

    /**
     * Initialises watcher for accrue value
     */
    function initAccrueValueWatcher () {
      if (parentCtrl.isMode('view')) { return; }

      $rootScope.$watch(
        function () { return parentCtrl.request.toil_to_accrue; },
        function (oldValue, newValue) {
          if (+oldValue !== +newValue) {
            parentCtrl.attemptCalculateBalanceChange();
          }
        });
    }

    /**
     * Initialize requestCanExpire according to admin setting
     * and request type.
     * @return {Promise}
     */
    function initRequestCanExpire () {
      return AbsenceType.canExpire(parentCtrl.request.type_id)
      .then(function (canExpire) {
        parentCtrl.requestCanExpire = canExpire;
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
          parentCtrl.toilAmounts = _.indexBy(amounts, 'value');
        });
    }

    /**
     * Extends the parent's setDatesFromUI() function
     *
     * @return {Promise}
     */
    function setDatesFromUIExtended () {
      return parentCtrl.calculateToilExpiryDate().catch($q.resolve);
    }

    /**
     * Updates expiry date when user changes it on ui
     */
    function updateExpiryDate () {
      if (parentCtrl.uiOptions.expiryDate) {
        parentCtrl.request.toil_expiry_date = parentCtrl.convertDateToServerFormat(parentCtrl.uiOptions.expiryDate);
      }
    }
  }
});
