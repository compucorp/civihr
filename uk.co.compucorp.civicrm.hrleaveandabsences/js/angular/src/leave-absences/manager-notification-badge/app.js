/* eslint-env amd */

define([
  'common/angular',
  'common/models/session.model',
  'common/services/pub-sub',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/components/leave-notification-badge.component',
  'leave-absences/manager-notification-badge/modules/config',
  'leave-absences/manager-notification-badge/components/manager-notification-badge.component'
], function (angular) {
  angular.module('manager-notification-badge', [
    'ngResource',
    'leave-absences.settings',
    'leave-absences.models',
    'leave-absences.components',
    'manager-notification-badge.components',
    'manager-notification-badge.config'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
