/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('leaveBalanceReport', {
    controller: balanceReportController,
    controllerAs: 'vm',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-balance-report.html';
    }]
  });

  function balanceReportController () {

  }
});
