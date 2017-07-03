/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/ui-router',
  'leave-absences/admin-dashboard/modules/config',
  'leave-absences/admin-dashboard/components/admin-dashboard-container',
  'leave-absences/admin-dashboard/components/admin-dashboard-calendar',
  'leave-absences/admin-dashboard/components/admin-dashboard-requests'
], function (angular) {
  angular.module('admin-dashboard', [
    'ngResource',
    'ui.bootstrap',
    'ui.router',
    'admin-dashboard.config',
    'admin-dashboard.components',
    'leave-absences.settings'
  ]).run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
