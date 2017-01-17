define([
  'common/angular',
  'common/angularBootstrap',
  'common/directives/loading',
  'leave-absences/shared/ui-router',
  'leave-absences/manager-leave/modules/config',
  'leave-absences/manager-leave/components/manager-leave',
  'leave-absences/manager-leave/components/manager-leave-calendar',
  'leave-absences/manager-leave/components/manager-leave-requests',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/leave-request-model',
], function (angular) {
  angular.module('manager-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.select',
    'ui.bootstrap',
    'common.models',
    'common.mocks',
    'common.directives',
    'manager-leave.config',
    'manager-leave.components',
    'leave-absences.directives',
    'leave-absences.models',
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
