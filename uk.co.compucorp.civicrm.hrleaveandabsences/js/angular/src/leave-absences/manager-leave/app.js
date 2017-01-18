define([
  'common/angular',
  'common/angularBootstrap',
  'common/directives/loading',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/manager-leave/modules/config',
  'leave-absences/manager-leave/components/manager-leave',
  'leave-absences/manager-leave/components/manager-leave-calendar',
  'leave-absences/manager-leave/components/manager-leave-requests'
], function (angular) {
  angular.module('manager-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.select',
    'ui.bootstrap',
    'common.angularDate',
    'common.models',
    'common.mocks',
    'common.directives',
    'leave-absences.models',
    'manager-leave.config',
    'manager-leave.components',
  ])
  .run(['$log', function ($log) {
    $log.debug('app.run');
  }]);

  return angular;
});
