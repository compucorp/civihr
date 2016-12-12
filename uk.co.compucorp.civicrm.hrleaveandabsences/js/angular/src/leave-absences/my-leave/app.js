define([
  'common/angular',
  'common/angularBootstrap',
  'leave-absences/shared/ui-router',
  'leave-absences/my-leave/modules/config',
  'leave-absences/my-leave/components/my-leave',
  'leave-absences/my-leave/components/my-leave-report'
], function (angular) {
  angular.module('my-leave', [
    'ngResource',
    'ui.bootstrap',
    'ui.router',
    'my-leave.config',
    'my-leave.components'
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
