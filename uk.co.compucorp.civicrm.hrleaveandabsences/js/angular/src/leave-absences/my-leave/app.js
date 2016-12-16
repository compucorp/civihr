define([
  'common/angular',
  'common/angularBootstrap',
  'leave-absences/shared/ui-router',
  'leave-absences/my-leave/modules/config',
  'leave-absences/my-leave/components/my-leave',
  'leave-absences/my-leave/components/my-leave-report',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/calender-model',
], function (angular) {
  angular.module('my-leave', [
      'ngResource',
      'ui.router',
      'my-leave.config',
      'my-leave.components',
      'leave-absences.models',
    ])
    .run(['$log', function ($log) {
      $log.debug('app.run');
    }]);

  return angular;
});
