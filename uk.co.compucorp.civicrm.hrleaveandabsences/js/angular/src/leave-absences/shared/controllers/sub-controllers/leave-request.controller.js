/* eslint-env amd */

define([
  'leave-absences/shared/modules/controllers'
], function (controllers) {
  controllers.controller('LeaveRequestCtrl', LeaveRequestCtrl);

  LeaveRequestCtrl.$inject = ['$controller', '$log', '$q', 'parentCtrl'];

  function LeaveRequestCtrl ($controller, $log, $q, parentCtrl) {
    $log.debug('LeaveRequestCtrl');

    parentCtrl.calculateBalanceChange = calculateBalanceChange;
    parentCtrl.canCalculateChange = canCalculateChange;
    parentCtrl.checkSubmitConditions = checkSubmitConditions;
    parentCtrl.initChildController = initChildController;

    /**
     * Calculates balance change
     *
     * @return {Promise}
     */
    function calculateBalanceChange () {
      return parentCtrl.request.calculateBalanceChange(parentCtrl.selectedAbsenceType.calculation_unit_name);
    }

    /**
     * Checks if change can be calculated
     *
     * @return {Boolean}
     */
    function canCalculateChange () {
      var canCalculate = !!parentCtrl.request.from_date && !!parentCtrl.request.to_date;

      if (parentCtrl.selectedAbsenceType.calculation_unit_name === 'days') {
        canCalculate = canCalculate &&
          !!parentCtrl.request.from_date_type && !!parentCtrl.request.to_date_type;
      }

      return canCalculate;
    }

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function checkSubmitConditions () {
      return parentCtrl.canCalculateChange();
    }

    /**
     * Initialize the controller
     *
     * @return {Promise}
     */
    function initChildController () {
      return $q.resolve();
    }
  }
});
