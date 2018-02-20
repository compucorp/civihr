/* eslint-env amd */

define([
  'leave-absences/shared/modules/controllers'
], function (controllers) {
  controllers.controller('RequestModalDetailsLeaveController', RequestModalDetailsLeaveController);

  RequestModalDetailsLeaveController.$inject = ['$controller', '$log', '$q', 'detailsController'];

  function RequestModalDetailsLeaveController ($controller, $log, $q, detailsController) {
    $log.debug('RequestModalDetailsLeaveController');

    detailsController.calculateBalanceChange = calculateBalanceChange;
    detailsController.canCalculateChange = canCalculateChange;
    detailsController.canSubmit = canSubmit;
    detailsController.initChildController = initChildController;

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
     * Initialize the controller
     *
     * @return {Promise}
     */
    function initChildController () {
      return $q.resolve();
    }
  }
});
