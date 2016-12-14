define([
  'leave-absences/my-leave/modules/components'
], function (components) {

  components.component('myLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-calendar.html';
    }],
    controllerAs: 'report',
    controller: ['$log', controller]
  });


  function controller($log) {
    $log.debug('Component: my-leave-calendar');
  }
});
