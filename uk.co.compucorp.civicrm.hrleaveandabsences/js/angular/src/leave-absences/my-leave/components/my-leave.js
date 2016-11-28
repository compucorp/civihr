define([
  'leave-absences/my-leave/modules/components'
], function (components) {
  components.component('myLeave', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave.html';
    }],
    controller: ['$log', function ($log) {
      console.log('CRM id of the currently logged in user: ', this.contactId);
      $log.debug('Component: my-leave');
    }]
  });
});
