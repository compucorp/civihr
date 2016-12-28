define([
  'leave-absences/manager-leave/modules/components'
], function (components) {

  components.component('managerLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$log', controller]
  });


  function controller($log) {
    $log.debug('Component: manager-leave-calendar');
  }
});
