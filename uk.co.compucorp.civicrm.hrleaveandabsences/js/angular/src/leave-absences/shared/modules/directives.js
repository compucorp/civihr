define([
  'common/angular',
  'common/angularBootstrap',
  'common/services/angular-date/date-format',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/modules/components',
], function (angular) {
  return angular.module('leave-absences.directives', [
    'ui.bootstrap',
    'common.angularDate',
    'leave-absences.settings',
    'leave-absences.components',
  ]);
});
