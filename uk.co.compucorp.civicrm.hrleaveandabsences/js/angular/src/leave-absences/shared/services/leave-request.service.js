/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/services',
  'leave-absences/shared/models/absence-type.model'
], function (_, services) {
  'use strict';

  services.factory('LeaveRequestService', LeaveRequestService);

  LeaveRequestService.$inject = [
    '$log', '$q', 'dialog', 'AbsenceType'
  ];

  function LeaveRequestService ($log, $q, dialog, AbsenceType) {
    $log.debug('LeaveRequest');

    return {
      checkIfBalanceChangeNeedsForceRecalculation: checkIfBalanceChangeNeedsForceRecalculation
    };

    /**
     * Checks if the balance change of the leave request need to be recalculated,
     * and if yes, prompts the recalculation via a dialog
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {Promise} resolves with {Boolean}:
     *   true if balance change has been changed and that was acknowledged;
     *   false if balance change has not been changed.
     */
    function checkIfBalanceChangeNeedsForceRecalculation (leaveRequest) {
      var originalBalanceAmount, unitName;

      return AbsenceType.all({ id: leaveRequest.type_id })
        .then(AbsenceType.loadCalculationUnits)
        .then(function (absenceTypes) {
          unitName = absenceTypes[0].calculation_unit_name;
        })
        .then(leaveRequest.getBalanceChangeBreakdown.bind(this))
        .then(function (originalBalance) {
          originalBalanceAmount = originalBalance.amount;

          return leaveRequest.calculateBalanceChange(unitName);
        })
        .then(function (balanceChange) {
          if (+originalBalanceAmount === +balanceChange.amount) {
            return $q.resolve(false);
          }

          return openConfirmationDialog();
        });
    }

    function openConfirmationDialog () {
      var defer = $q.defer();

      dialog.open({
        title: 'The balance has been changed',
        copyCancel: 'Cancel',
        copyConfirm: 'Review changes',
        classConfirm: 'btn-primary',
        msg: 'The balance has been changed',
        onConfirm: function () {
          defer.resolve(true);
        }
      });

      return defer.promise;
    }
  }
});
