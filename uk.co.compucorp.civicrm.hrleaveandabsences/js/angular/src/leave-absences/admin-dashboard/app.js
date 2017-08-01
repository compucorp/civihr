/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/modules/dialog',
  'common/services/check-permissions',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/components/leave-calendar.component',
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/shared/components/leave-calendar-legend.component',
  'leave-absences/shared/components/leave-calendar-month.component',
  'leave-absences/shared/components/leave-request-actions.component',
  'leave-absences/shared/components/leave-request-record-actions.component',
  'leave-absences/shared/components/leave-request-popup-comments-tab',
  'leave-absences/shared/components/leave-request-popup-files-tab',
  'leave-absences/shared/components/manage-leave-requests',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/admin-dashboard/modules/config',
  'leave-absences/admin-dashboard/components/admin-dashboard-container'
], function (angular) {
  angular.module('admin-dashboard', [
    'ngAnimate',
    'ngResource',
    'ui.bootstrap',
    'ui.router',
    'ui.select',
    'textAngular',
    'common.dialog',
    'leave-absences.settings',
    'leave-absences.components',
    'leave-absences.directives',
    'admin-dashboard.config',
    'admin-dashboard.components'
  ]).run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
