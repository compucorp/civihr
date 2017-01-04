define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/directives/loading',
  'common/models/option-group',
  'common/modules/dialog',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/my-leave/modules/config',
  'leave-absences/my-leave/components/my-leave',
  'leave-absences/my-leave/components/my-leave-calendar',
  'leave-absences/my-leave/components/my-leave-report',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/public-holiday-model',
  'common/services/angular-date/date-format',
], function (angular) {
  angular.module('my-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.directives',
    'common.models',
    'my-leave.config',
    'my-leave.components',
    'leave-absences.directives',
    'leave-absences.models',
    'common.angularDate',
  ])
  .run(['$log','DateFormat', function ($log, DateFormat) {
    $log.debug('app.run');
    //set HR_Settings
    DateFormat.getDateFormat().then(function(result){
      //sets the HR_Settings to default
    });
  }]);

  return angular;
});
