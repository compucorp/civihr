/* eslint-env amd */

define([
  'common/angular',
  'common/modules/directives',
  'common/modules/models',
  './shared-settings',
  './models'
], function (angular) {
  return angular.module('leave-absences.components', [
    'common.directives',
    'common.models',
    'leave-absences.settings',
    'leave-absences.models'
  ]);
});
