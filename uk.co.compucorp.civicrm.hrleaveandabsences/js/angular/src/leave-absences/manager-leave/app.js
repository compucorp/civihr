/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/directives/loading',
  'common/modules/dialog',
  'common/services/angular-date/date-format',
  'common/services/check-permissions',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/components/leave-balance-report.component',
  'leave-absences/shared/components/leave-request-actions.component',
  'leave-absences/shared/components/leave-request-popup-comments-tab.component',
  'leave-absences/shared/components/leave-request-popup-files-tab',
  'leave-absences/shared/components/leave-request-record-actions.component',
  'leave-absences/shared/components/manage-leave-requests.component',
  'leave-absences/shared/components/manager-leave-calendar',
  'leave-absences/shared/directives/leave-request-popup.directive',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/services/leave-popup.service',
  'leave-absences/manager-leave/modules/config',
  'leave-absences/manager-leave/components/manager-leave-container'
], function (angular) {
  angular.module('manager-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.select',
    'ui.bootstrap',
    'textAngular',
    'common.angularDate',
    'common.models',
    'common.mocks',
    'common.directives',
    'common.dialog',
    'leave-absences.models',
    'manager-leave.config',
    'manager-leave.components',
    'leave-absences.components',
    'leave-absences.directives',
    'leave-absences.models',
    'leave-absences.services'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
