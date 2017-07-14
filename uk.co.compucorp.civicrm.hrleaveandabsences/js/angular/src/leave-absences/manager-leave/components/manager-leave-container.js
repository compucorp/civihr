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
    controller: ['$log', function ($log) {
      $log.debug('Component: manager-leave-container');

      var vm = Object.create(this);

      return vm;
    }],
    controllerAs: 'managerLeave'
  });
});
