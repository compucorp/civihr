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
    controller: ['$log', '$rootScope', '$state', function ($log, $rootScope, $state) {
      $log.debug('Component: manager-leave-container');

      $rootScope.section = 'manager-leave';

      var vm = this;

      vm.tabName = $state.current.name;

      vm.changeTab = changeTab;

      /**
       * Change the ui-router route
       */
      function changeTab () {
        $state.go(vm.tabName);
      }
    }],
    controllerAs: 'managerLeave'
  });
});
