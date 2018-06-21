/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  return angular.module('calendar-feeds.core', [
    'leave-absences.settings'
  ]);
});
