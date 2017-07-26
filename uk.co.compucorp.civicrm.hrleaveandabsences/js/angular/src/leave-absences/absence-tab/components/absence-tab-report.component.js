/* eslint-env amd */

define([
  'leave-absences/absence-tab/modules/components'
], function (components) {
  components.component('absenceTabReport', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-report.html';
    }],
    controllerAs: 'report',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: absence-tab-report');

    var vm = this;
  }
});
