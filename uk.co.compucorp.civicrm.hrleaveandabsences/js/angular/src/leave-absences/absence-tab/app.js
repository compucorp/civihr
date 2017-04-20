define([
  'common/angular',
  'common/angularBootstrap',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/absence-tab/modules/settings',
  'leave-absences/absence-tab/components/absence-tab-container',
  'leave-absences/absence-tab/components/absence-tab-report',
  'leave-absences/absence-tab/components/absence-tab-calendar',
  'leave-absences/absence-tab/components/absence-tab-entitlements',
  'leave-absences/absence-tab/components/absence-tab-work-patterns',
], function (angular) {
  angular.module('absence-tab', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'common.angularDate',
    'common.directives',
    'absence-tab.settings',
    'absence-tab.components'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
