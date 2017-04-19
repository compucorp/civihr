define([
  'leave-absences/absence-tab/modules/components',
], function (components) {

  components.component('absenceTabWorkPatterns', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-work-patterns.html';
    }],
    controllerAs: 'workpatterns',
    controller: ['$log', controller]
  });

  function controller($log) {
    $log.debug('Component: absence-tab-work-patterns');

    var vm = {};

    return vm;
  }
});
