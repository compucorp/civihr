/* eslint-env amd */

define([
  'leave-absences/shared/modules/controllers'
], function (controllers) {
  controllers.controller('LeaveRequestCtrl', LeaveRequestCtrl);

  LeaveRequestCtrl.$inject = ['$controller', '$log', '$q', 'parentCtrl'];

  function LeaveRequestCtrl ($controller, $log, $q, parentCtrl) {
    $log.debug('LeaveRequestCtrl');

    parentCtrl.calculateBalanceChange = calculateBalanceChange;
    parentCtrl.checkSubmitConditions = checkSubmitConditions;
    parentCtrl.initChildController = initChildController;

    /**
     * Calculate change in balance, it updates local balance variables.
     *
     * @return {Promise} empty promise if all required params are not set
     *   otherwise promise from server
     */
    function calculateBalanceChange () {
      return parentCtrl.request.calculateBalanceChange(parentCtrl.selectedAbsenceType.calculation_unit_name);
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
