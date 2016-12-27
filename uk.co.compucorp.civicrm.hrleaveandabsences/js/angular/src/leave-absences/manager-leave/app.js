define([
  'common/angular',
  'common/angularBootstrap',
  'leave-absences/shared/ui-router',
  'leave-absences/manager-leave/modules/config',
  'leave-absences/manager-leave/components/manager-leave',
  'leave-absences/manager-leave/components/manager-leave-calendar',
  'leave-absences/manager-leave/components/manager-leave-requests',
], function (angular) {
  angular.module('manager-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'manager-leave.config',
    'manager-leave.components',
  ])
  .run(['$log', function ($log) {
    // $log.debug('app.run');
  }]);

  return angular;
});
