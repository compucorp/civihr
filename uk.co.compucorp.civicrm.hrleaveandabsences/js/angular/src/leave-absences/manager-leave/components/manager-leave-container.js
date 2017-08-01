/* eslint-env amd */

define([
  'leave-absences/manager-leave/modules/components'
], function (components) {
  components.component('managerLeaveContainer', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave-container.html';
    }],
    controller: ['$log', '$rootScope', function ($log, $rootScope) {
      $log.debug('Component: manager-leave-container');

      $rootScope.section = 'manager-leave';
    }],
    controllerAs: 'managerLeave'
  });
});
