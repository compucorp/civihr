/* eslint-env amd */

define([
  'common/angular',
  'common/models/session.model',
  'common/services/pub-sub',
  'common/components/notification-badge.component',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/models/leave-request.model',
  'leave-absences/manager-notification-badge/modules/config',
  'leave-absences/manager-notification-badge/components/manager-notification-badge.component'
], function (angular) {
  angular.module('manager-notification-badge', [
    'ngResource',
    'common.components',
    'common.templates',
    'leave-absences.settings',
    'leave-absences.models',
    'manager-notification-badge.components',
    'manager-notification-badge.config'
  ])
    .run(['$log', function ($log) {
      $log.debug('app.run');
    }]);

  return angular;
});
