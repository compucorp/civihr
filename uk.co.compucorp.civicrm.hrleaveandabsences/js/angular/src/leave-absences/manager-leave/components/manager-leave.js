define([
  'leave-absences/manager-leave/modules/components'
], function (components) {
  components.component('managerLeave', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave.html';
    }],
    controllerAs: 'managerLeave',
    controller: ['$log', function ($log) {
      console.log('CRM id of the currently logged in user: ', this.contactId);
      $log.debug('Component: manager-leave');

      var vm = {};

      return vm;
    }]
  });
});
