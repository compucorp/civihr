/* eslint-env amd */

define([
  'common/angular',
  './shared-settings',
  './models'
], function (angular) {
  return angular.module('leave-absences.components', [
    'leave-absences.settings',
    'leave-absences.models'
  ]);
});
