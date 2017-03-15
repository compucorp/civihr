define([
  'common/angular',
  'common/models/instances/instance',
  'common/modules/services',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models.instances', [
    'common.models.instances',
    'common.services',
    'leave-absences.settings'
  ]);
});
