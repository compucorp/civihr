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
    controller: ['$log', '$rootScope', '$state', function ($log, $rootScope, $state) {
      $log.debug('Component: my-leave-container');

      $rootScope.section = 'my-leave';

      var vm = this;

      vm.tabName = $state.current.name;

      vm.changeTab = changeTab;

      /**
       * Change the ui-router route
       */
      function changeTab () {
        $state.go(vm.tabName);
      }
    }]
  });
});
