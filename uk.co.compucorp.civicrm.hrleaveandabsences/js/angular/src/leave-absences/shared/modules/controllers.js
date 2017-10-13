define([
  'common/angular',
  'common/angularBootstrap',
  'leave-absences/shared/modules/models',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  return angular.module('leave-absences.controllers', [
    'ui.select',
    'leave-absences.models',
    'leave-absences.models.instances',
    'leave-absences.settings',
  ]);
});
