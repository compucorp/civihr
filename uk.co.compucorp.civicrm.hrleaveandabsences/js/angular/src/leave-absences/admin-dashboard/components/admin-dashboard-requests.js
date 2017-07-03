/* eslint-env amd */

define([
  'leave-absences/admin-dashboard/modules/components'
], function (components) {
  components.component('adminDashboardRequests', {
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/admin-dashboard-requests.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: admin-dashboard-requests');

    var vm = Object.create(this);

    return vm;
  }
});
