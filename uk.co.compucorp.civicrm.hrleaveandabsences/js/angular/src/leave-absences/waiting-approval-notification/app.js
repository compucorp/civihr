/* eslint-env amd */

define([
  'common/angular',
  'common/services/pub-sub',
  'common/services/session',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/components/leave-notification.component',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/waiting-approval-notification/components/waiting-approval-notification.component',
  'leave-absences/waiting-approval-notification/modules/config'
], function (angular) {
  angular.module('waiting-approval-notification', [
    'ngResource',
    'leave-absences.components',
    'leave-absences.models',
    'leave-absences.settings',
    'waiting-approval-notification.components',
    'waiting-approval-notification.config'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
