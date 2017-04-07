define([
  'common/angular',
  'common/angularBootstrap',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/absence-tab/modules/config',
  'leave-absences/absence-tab/components/absence-tab-container',
], function (angular) {
  angular.module('absence-tab', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'common.angularDate',
    'common.directives',
    'common.models',
    // 'leave-absences.directives',
    // 'leave-absences.models',
    // 'leave-absences.settings',
    'absence-tab.config',
    'absence-tab.components'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
