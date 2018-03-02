/* eslint-env amd */

define([
  'common/angular',
  'common/modules/directives',
  'common/modules/models',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/modules/models'
], function (angular) {
  return angular.module('leave-absences.components', [
    'common.directives',
    'common.models',
    'leave-absences.settings',
    'leave-absences.models'
  ]);
});
