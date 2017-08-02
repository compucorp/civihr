/* eslint-env amd */

define([
  'leave-absences/my-leave/modules/components'
], function (components) {
  components.component('myLeaveContainer', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-container.html';
    }],
    controllerAs: 'myleave',
    controller: ['$log', '$rootScope', function ($log, $rootScope) {
      $log.debug('Component: my-leave-container');

      $rootScope.section = 'my-leave';
    }]
  });
});
