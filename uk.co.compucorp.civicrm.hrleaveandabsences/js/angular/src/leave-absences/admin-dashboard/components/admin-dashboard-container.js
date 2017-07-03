/* eslint-env amd */

define([
  'leave-absences/admin-dashboard/modules/components'
], function (components) {
  components.component('adminDashboardContainer', {
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/admin-dashboard-container.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', '$rootScope', controller]
  });

  function controller ($log, $rootScope) {
    $log.debug('Component: admin-dashboard-container');

    $rootScope.role = 'admin';

    var vm = Object.create(this);

    return vm;
  }
});
