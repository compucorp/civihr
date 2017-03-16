define([
  'common/angular',
  'common/modules/models',
  'common/modules/services',
  'common/angular-file-upload',
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models', [
    'common.models',
    'common.services',
    'angularFileUpload',
    'leave-absences.apis',
    'leave-absences.models.instances',
    'leave-absences.settings',
  ]);
});
