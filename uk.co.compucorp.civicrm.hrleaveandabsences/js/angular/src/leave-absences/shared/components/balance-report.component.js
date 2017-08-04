/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('balanceReport', {
    controller: balanceReportController,
    controllerAs: 'vm',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/balance-report.html';
    }]
  });

  function balanceReportController () {

  }
});
