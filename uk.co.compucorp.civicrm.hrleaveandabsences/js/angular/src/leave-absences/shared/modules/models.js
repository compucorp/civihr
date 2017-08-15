define([
  'common/angular',
  'common/modules/models',
  'common/modules/services',
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
  'mocks/apis/leave-balance-report-api-mock' // TODO: remove mock
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models', [
    'common.models',
    'common.services',
    'leave-absences.apis',
    'leave-absences.models.instances',
    'leave-absences.settings',
    'leave-absences.mocks' // TODO: remove mock
  ]);
});
