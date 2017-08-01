/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/modules/dialog',
  'common/services/angular-date/date-format',
  'common/services/check-permissions',
  'common/directives/loading',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
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
  'leave-absences/manager-leave/modules/config',
  'leave-absences/manager-leave/components/manager-leave-container'
], function (angular) {
  angular.module('manager-leave', [
    'ngResource',
    'ngAnimate',
    'ui.bootstrap',
    'ui.router',
    'ui.select',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.models',
    'common.directives',
    'common.mocks',
    'leave-absences.models',
    'leave-absences.components',
    'leave-absences.directives',
    'manager-leave.config',
    'manager-leave.components'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
