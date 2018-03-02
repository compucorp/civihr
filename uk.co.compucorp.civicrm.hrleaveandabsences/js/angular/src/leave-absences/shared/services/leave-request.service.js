/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/services'
], function (_, services) {
  'use strict';

  services.factory('LeaveRequestService', LeaveRequestService);

  LeaveRequestService.$inject = ['$log', '$q', 'dialog'];

  function LeaveRequestService ($log, $q, dialog) {
    $log.debug('LeaveRequest');

    return {
      getBalanceChangeRecalculationPromptOptions: getBalanceChangeRecalculationPromptOptions,
      promptIfProceedWithBalanceChangeRecalculation: promptIfProceedWithBalanceChangeRecalculation
    };

    /**
     * Prompts the user if they would like to proceed with
     * balance change recalculation via a dialog
     *
     * @return {Promise}
     */
    function promptIfProceedWithBalanceChangeRecalculation () {
      var deferred = $q.defer();

      dialog.open(_.defaults(getBalanceChangeRecalculationPromptOptions(), {
        onConfirm: function () {
          deferred.resolve(true);
        }
      }));

      return deferred.promise;
    }

    /**
     * Returns properties for the balance change recalculation prompt dialog
     */
    function getBalanceChangeRecalculationPromptOptions () {
      return {
        title: 'Recalculate Balance Change?',
        copyCancel: 'Cancel',
        copyConfirm: 'Yes',
        classConfirm: 'btn-warning',
        msg: 'The leave balance change has updated since ' +
          'this leave request was created. ' +
          'Do you want to recalculate the balance change?'
      };
    }
  }
});
