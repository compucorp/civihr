define([
  'leave-absences/absence-tab/modules/components',
], function (components) {

  components.component('absenceTabEntitlements', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-entitlements.html';
    }],
    controllerAs: 'entitlements',
    controller: ['$log', controller]
  });

  function controller($log) {
    $log.debug('Component: absence-tab-entitlements');

    var vm = {};

    return vm;
  }
});
