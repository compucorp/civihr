define([
  'leave-absences/my-leave/modules/components'
], function (components) {

  components.component('myLeaveReport', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-report.html';
    }],
    controllerAs: 'report',
    controller: ['$log', controller]
  });


  function controller($log) {
    $log.debug('Component: my-leave-report');

    init.call(this);

    /**
     * Init code
     */
    function init() {
      this.isOpen = {
        approved: false,
        entitlement: false,
        holiday: false,
        open: false,
        other: false
      };
    }
  }
});
