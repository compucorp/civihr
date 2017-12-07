/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers'
], function (_, controllers) {
  controllers.controller('ToilRequestCtrl', ToilRequestCtrl);

  ToilRequestCtrl.$inject = ['$log', '$q', '$rootScope', 'api.optionGroup', 'AbsenceType', 'parentCtrl'];

  function ToilRequestCtrl ($log, $q, $rootScope, OptionGroup, AbsenceType, parentCtrl) {
    $log.debug('ToilRequestCtrl');

    var vm = parentCtrl;

    vm.requestCanExpire = true;

    vm.calculateBalanceChange = calculateBalanceChange;
    vm.calculateToilExpiryDate = calculateToilExpiryDate;
    vm.canCalculateChange = canCalculateChange;
    vm.changeInNoOfDaysExtended = changeInNoOfDaysExtended;
    vm.checkSubmitConditions = checkSubmitConditions;
    vm.clearExpiryDate = clearExpiryDate;
    vm.initChildController = initChildController;
    vm.setDatesFromUIExtended = setDatesFromUIExtended;
    vm.updateExpiryDate = updateExpiryDate;

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
      vm.balance.change.amount = +vm.request.toil_to_accrue;

      return $q.resolve(vm.balance.change);
    }

    /**
     * Calculates toil expiry date.
     *
     * @return {Promise}
     */
    function calculateToilExpiryDate () {
      // blocks the expiry date from updating if this is an existing request
      // and user is not a manager or admin
      if (!vm.canManage && vm.request.id) {
        return $q.resolve(vm.request.toil_expiry_date);
      }

      // skips calculation of expiration date if request never expires
      // according to admin setting
      if (!vm.requestCanExpire) {
        vm.request.toil_expiry_date = false;
        return $q.resolve(false);
      }

      return getReferenceDate().catch(function (errors) {
        if (errors.length) vm.errors = errors;
        return $q.reject(errors);
      }).then(function (referenceDate) {
        return AbsenceType.calculateToilExpiryDate(
          vm.request.type_id,
          referenceDate
        );
      })
      .then(function (expiryDate) {
        vm.request.toil_expiry_date = expiryDate;
        vm.uiOptions.expiryDate = new Date(expiryDate);

        return expiryDate;
      });
    }

    /**
     * Checks if change can be calculated
     *
     * @return {Boolean}
     */
    function canCalculateChange () {
      return !!vm.request.toil_to_accrue;
    }

    /**
     * Determines if the expiry date can be calculated based on the
     * Number Of Days selected and the corresponding date field has value.
     *
     * @return {Boolean}
     */
    function canCalculateExpiryDate () {
      return (vm.uiOptions.multipleDays && vm.request.to_date) ||
        (!vm.uiOptions.multipleDays && vm.request.from_date);
    }

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function checkSubmitConditions () {
      return !!vm.request.from_date && !!vm.request.to_date &&
        !!vm.request.toil_duration && !!vm.request.toil_to_accrue;
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
      vm.request.toil_expiry_date = false;
      vm.uiOptions.expiryDate = null;
    }

    /**
     * Returns a promise with a date that can be used to calculate the expiry
     * date. This date depends on the Multiple Days or Single Day options.
     *
     * @return {Promise}
     */
    function getReferenceDate () {
      if (vm.uiOptions.multipleDays) {
        return getReferenceDateForField({
          hasErrors: !vm.request.to_date && !vm.request.from_date,
          label: 'To Date',
          value: vm.request.to_date
        });
      } else {
        return getReferenceDateForField({
          hasErrors: !vm.request.from_date,
          label: 'From Date',
          value: vm.request.from_date
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
      vm.request.to_date_type = vm.request.from_date_type = '1';

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
      if (vm.canManage) {
        vm.uiOptions.expiryDate = vm.convertDateFormatFromServer(vm.request.toil_expiry_date);
      }
    }

    /**
     * Initialises watcher for accrue value
     */
    function initAccrueValueWatcher () {
      if (vm.isMode('view')) { return; }

      $rootScope.$watch(
        function () { return vm.request.toil_to_accrue; },
        function (oldValue, newValue) {
          if (+oldValue !== +newValue) {
            vm.attemptCalculateBalanceChange();
          }
        });
    }

    /**
     * Initialize requestCanExpire according to admin setting
     * and request type.
     * @return {Promise}
     */
    function initRequestCanExpire () {
      return AbsenceType.canExpire(vm.request.type_id)
      .then(function (canExpire) {
        vm.requestCanExpire = canExpire;
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
          vm.toilAmounts = _.indexBy(amounts, 'value');
        });
    }

    /**
     * Extends the parent's setDatesFromUI() function
     *
     * @return {Promise}
     */
    function setDatesFromUIExtended () {
      return vm.calculateToilExpiryDate().catch($q.resolve);
    }

    /**
     * Updates expiry date when user changes it on ui
     */
    function updateExpiryDate () {
      if (vm.uiOptions.expiryDate) {
        vm.request.toil_expiry_date = vm.convertDateToServerFormat(vm.uiOptions.expiryDate);
      }
    }
  }
});
