define([
  'leave-absences/absence-tab/modules/components'
], function (components) {
  components.component('absenceTab', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab.html';
    }],
    controllerAs: 'absence',
    controller: ['$log', 'settings', function ($log) {
      $log.debug('Component: absence-tab');

      var vm = {};

      return vm;
    }]
  });
});
