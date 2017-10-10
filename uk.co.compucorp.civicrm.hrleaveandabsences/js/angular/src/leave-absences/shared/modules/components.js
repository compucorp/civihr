/* eslint-env amd */

define([
  'common/angular',
  './shared-settings'
], function (angular) {
  return angular.module('leave-absences.components', [
    'leave-absences.settings'
  ]);
});
