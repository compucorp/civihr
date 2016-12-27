define([
  'leave-absences/manager-leave/modules/components'
], function (components) {

  components.component('managerLeaveRequests', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave-requests.html';
    }],
    controllerAs: 'requests',
    controller: ['$log', controller]
  });


  function controller($log) {
    $log.debug('Component: manager-leave-requests');
  }
});
