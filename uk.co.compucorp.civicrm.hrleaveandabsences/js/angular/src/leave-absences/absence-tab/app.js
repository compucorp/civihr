/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/modules/directives',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/absence-tab/modules/config',
  'leave-absences/absence-tab/components/absence-tab-container',
  'leave-absences/absence-tab/components/absence-tab-report',
  'leave-absences/absence-tab/components/absence-tab-entitlements',
  'leave-absences/absence-tab/components/absence-tab-work-patterns',
  'leave-absences/shared/components/staff-leave-calendar',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  angular.module('absence-tab', [
    'ngResource',
    'ui.bootstrap',
    'common.directives',
    'absence-tab.config',
    'absence-tab.components',
    'leave-absences.components',
    'leave-absences.controllers',
    'leave-absences.models',
    'leave-absences.settings'
  ]).run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
