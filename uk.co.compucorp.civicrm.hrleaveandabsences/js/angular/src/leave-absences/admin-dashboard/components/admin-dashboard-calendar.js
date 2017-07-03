/* eslint-env amd */

define([
  'leave-absences/admin-dashboard/modules/components'
], function (components) {
  components.component('adminDashboardCalendar', {
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/admin-dashboard-calendar.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: admin-dashboard-calendar');

    var vm = Object.create(this);

    return vm;
  }
});
