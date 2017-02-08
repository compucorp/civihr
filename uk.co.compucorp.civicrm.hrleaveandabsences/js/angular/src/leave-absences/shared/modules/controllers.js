define([
  'common/angular',
  'leave-absences/shared/modules/models',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  return angular.module('leave-absences.controllers', [
    'leave-absences.models',
    'leave-absences.models.instances',
    'leave-absences.settings'
  ]);
});
