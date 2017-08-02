/* eslint-env amd */

define([
  'common/angular',
  'common/services/pub-sub',
  'common/services/session',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/components/leave-notification-badge.component',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/manager-notification-badge/components/manager-notification-badge.component',
  'leave-absences/manager-notification-badge/modules/config'
], function (angular) {
  angular.module('manager-notification-badge', [
    'ngResource',
    'leave-absences.components',
    'leave-absences.models',
    'leave-absences.settings',
    'manager-notification-badge.components',
    'manager-notification-badge.config'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
