define([
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/toil-leave-request-instance',
], function (_, controllers) {
  controllers.controller('ToilRequestCtrl', [
    '$controller', '$log', '$q', '$uibModalInstance', 'api.optionGroup', 'AbsenceType', 'directiveOptions', 'TOILRequestInstance',
    function ($controller, $log, $q, $modalInstance, OptionGroup, AbsenceType, directiveOptions, TOILRequestInstance) {
      $log.debug('ToilRequestCtrl');

      var parentRequestCtrl = $controller('RequestCtrl'),
        vm = Object.create(parentRequestCtrl);

      vm.directiveOptions = directiveOptions;
      vm.$modalInstance = $modalInstance;
      vm.leaveType = 'toil';
      vm.initParams = {
        absenceType: {
          allow_accruals_request: true
        }
      };

      /**
       * Calculate change in balance, it updates balance variables.
       * It overrides the parent's implementation
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      vm.calculateBalanceChange = function () {
        if (vm.request.toil_to_accrue) {
          vm.loading.calculateBalanceChange = true;
          vm.balance.change.amount = -vm.request.toil_to_accrue;
          vm.balance.closing = vm.balance.opening + vm.balance.change.amount;
          vm.uiOptions.showBalance = true;
          vm.loading.calculateBalanceChange = false;
        }
      };

      /**
       * Calculates toil expiry date.
       * TODO It will be based on from date for both single and multiple days for now.
       *
       * @return {Promise}
       */
      vm.calculateToilExpiryDate = function () {
        if (!vm.request.from_date) {
          vm.error = 'Please select from date to find expiry date';
          return $q.reject(vm.error);
        }

        return AbsenceType.calculateToilExpiryDate(vm.request.type_id, vm.request.from_date)
          .then(function (expiryDate) {
            vm.expiryDate = expiryDate;
          });
      };

      /**
       * Checks if submit button can be enabled for user and returns true if successful
       *
       * @return {Boolean}
       */
      vm.canSubmit = function () {
        return !!vm.request.duration && !!vm.request.toil_to_accrue &&
          !!vm.request.from_date && !!vm.request.to_date;
      };

      /**
       * This should be called whenever a date has been changed
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @return {Promise}
       */
      vm.updateAbsencePeriodDatesTypes = function (date) {
        var oldPeriodId = vm.period.id;

        return vm._checkAndSetAbsencePeriod(date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId == vm.period.id;

            if (!isInCurrentPeriod) {
              if (vm.uiOptions.multipleDays) {
                vm.uiOptions.showBalance = false;
                vm.uiOptions.toDate = null;
                vm.request.to_date = null;
              }

              return $q.all([
                vm._loadAbsenceTypes(),
                vm._loadCalendar()
              ]);
            }
          })
          .then(function () {
            vm._setMinMaxDate();
            vm._setDates();
            vm.calculateToilExpiryDate();
          })
          .catch(function (error) {
            vm.error = error;
          });
      };

      /**
       * Resets data for toil.
       */
      vm._reset = function () {
        parentRequestCtrl._reset.call(this);
        vm.request.toilDurationHours = 0;
        vm.request.toilDurationMinutes = 0;
        vm.request.updateDuration();
        vm.request.toil_to_accrue = "";
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;
        initRequest();

        vm._init()
          .then(function () {
            return loadToilAmounts();
          })
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      function initRequest() {
        var attributes = vm._initRequestAttributes();

        vm.request = TOILRequestInstance.init(attributes);
        //required by leave request so set it to All Day
        vm.request.to_date_type = vm.request.from_date_type = 1;
      }

      /**
       * Initializes leave request toil amounts
       *
       * @return {Promise}
       */
      function loadToilAmounts() {
        return OptionGroup.valuesOf('hrleaveandabsences_toil_amounts')
          .then(function (amounts) {
            vm.toilAmounts = _.indexBy(amounts, 'value');
          });
      }

      return vm;
    }
  ]);
});
