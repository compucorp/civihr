/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/modules/dialog',
  'common/services/check-permissions',
  'common/services/angular-date/date-format',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/components/leave-balance-report.component',
  'leave-absences/shared/components/leave-request-actions.component',
  'leave-absences/shared/components/leave-request-popup-comments-tab.component',
  'leave-absences/shared/components/leave-request-popup-files-tab',
  'leave-absences/shared/components/leave-request-record-actions.component',
  'leave-absences/shared/components/manager-leave-calendar',
  'leave-absences/shared/components/manage-leave-requests.component',
  'leave-absences/shared/controllers/sub-controllers/leave-request.controller',
  'leave-absences/shared/controllers/sub-controllers/sick-request.controller',
  'leave-absences/shared/controllers/sub-controllers/toil-request.controller',
  'leave-absences/shared/services/leave-popup.service',
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
    'common.angularDate',
    'common.dialog',
    'common.mocks',
    'admin-dashboard.config',
    'admin-dashboard.components',
    'leave-absences.components',
    'leave-absences.controllers',
    'leave-absences.services',
    'leave-absences.settings'
  ]).run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
