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

      // TODO use `checkPermissions` service in the individual components instead
      $rootScope.role = 'manager';

      var vm = Object.create(this);

      return vm;
    }],
    controllerAs: 'managerLeave'
  });
});
