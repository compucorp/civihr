define([
  'leave-absences/absence-tab/modules/components',
], function (components) {

  components.component('absenceTabCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$log', controller]
  });

  function controller($log) {
    $log.debug('Component: absence-tab-calendar');

    var vm = {};

    return vm;
  }
});
