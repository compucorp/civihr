/* eslint-env amd */

define([
  'common/angular',
  'common/models/instances/instance',
  'common/modules/services',
  'common/modules/models',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models.instances', [
    'common.models',
    'common.models.instances',
    'common.services',
    'leave-absences.settings'
  ]);
});
