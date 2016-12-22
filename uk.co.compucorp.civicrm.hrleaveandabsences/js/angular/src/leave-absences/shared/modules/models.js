define([
  'common/angular',
  'common/modules/models',
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/modules/models-instances',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models', [
    'common.models',
    'common.services',
    'leave-absences.apis',
    'leave-absences.models.instances'
  ]);
});
