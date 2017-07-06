/* eslint-env amd */

define([
  'leave-absences/admin-dashboard/modules/components'
], function (components) {
  components.component('adminDashboardContainer', {
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/admin-dashboard-container.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: admin-dashboard-container');

    var vm = Object.create(this);

    return vm;
  }
});
