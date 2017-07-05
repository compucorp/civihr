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
  'leave-absences/shared/components/leave-request-popup-comments-tab',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/manager-leave/modules/config',
  'leave-absences/manager-leave/components/manager-leave-container',
  'leave-absences/shared/components/manager-leave-calendar',
  'leave-absences/manager-leave/components/manager-leave-requests'
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
    'leave-absences.models'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
