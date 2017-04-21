define([
  'common/angular',
  'common/angularBootstrap',
  'common/modules/directives',
  'leave-absences/absence-tab/modules/config',
  'leave-absences/absence-tab/components/absence-tab-container',
  'leave-absences/absence-tab/components/absence-tab-report',
  'leave-absences/absence-tab/components/absence-tab-calendar',
  'leave-absences/absence-tab/components/absence-tab-entitlements',
  'leave-absences/absence-tab/components/absence-tab-work-patterns',
], function (angular) {
  angular.module('absence-tab', [
    'ngResource',
    'ui.bootstrap',
    'common.directives',
    'absence-tab.config',
    'absence-tab.components'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
