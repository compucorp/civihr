define([
  'leave-absences/my-leave/modules/components'
], function (components) {
  components.component('myLeave', {
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave.html';
    }],
    controller: ['$log', function ($log) {
      $log.debug('Component: my-leave');
    }]
  });
})
