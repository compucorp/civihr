/* eslint-env amd */

define([
  'leave-absences/shared/modules/controllers'
], function (controllers) {
  controllers.controller('RequestModalDetailsLeaveController', RequestModalDetailsLeaveController);

  RequestModalDetailsLeaveController.$inject = ['$controller', '$log', '$q', 'parentCtrl'];

  function RequestModalDetailsLeaveController ($controller, $log, $q, parentCtrl) {
    $log.debug('RequestModalDetailsLeaveController');

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
     * Checks if the balance change can be calculated.
     * Any request of "leave" type requires dates.
     * Requests in "days" also require date types.
     * Requests in "hours" also require deductions
     *
     * @return {Boolean}
     */
    function canCalculateChange () {
      var request = parentCtrl.request;
      var canCalculate = !!request.from_date && !!request.to_date;
      var unit = parentCtrl.selectedAbsenceType.calculation_unit_name;

      if (unit === 'days') {
        canCalculate = canCalculate &&
          !!request.from_date_type && !!request.to_date_type;
      }

      if (unit === 'hours') {
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
