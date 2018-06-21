/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/calendar-feeds/calendar-feeds.apis',
  'leave-absences/calendar-feeds/calendar-feeds.models'
], function (angular) {
  return angular.module('calendar-feeds.core', [
    'leave-absences.settings',
    'calendar-feeds.apis',
    'calendar-feeds.models'
  ]);
});
