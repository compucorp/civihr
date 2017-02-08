define([
  'common/angular',
  'common/modules/models',
  'common/modules/services',
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models', [
    'common.models',
    'common.services',
    'leave-absences.apis',
    'leave-absences.models.instances',
    'leave-absences.settings'
  ]);
});
