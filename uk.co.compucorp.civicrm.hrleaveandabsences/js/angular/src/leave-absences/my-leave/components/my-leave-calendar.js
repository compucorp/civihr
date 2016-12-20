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
    controllerAs: 'calendar',
    controller: ['$log', controller]
  });


  function controller($log) {
    $log.debug('Component: my-leave-calendar');

    init.call(this);

    /**
     * Init code
     */
    function init() {
      this.legendCollapsed = false;
    }
  }
});
