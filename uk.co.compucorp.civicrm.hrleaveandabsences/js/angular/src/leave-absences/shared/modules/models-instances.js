define([
  'common/angular',
  'common/models/instances/instance',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models.instances', [
    'common.models.instances',
    'leave-absences.settings'
  ]);
});
