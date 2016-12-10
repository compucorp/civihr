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
    controller: ['$log', function ($log) {
      console.log('CRM id of the currently logged in user: ', this.contactId);
      $log.debug('Component: my-leave-report');
    }]
  });
});
