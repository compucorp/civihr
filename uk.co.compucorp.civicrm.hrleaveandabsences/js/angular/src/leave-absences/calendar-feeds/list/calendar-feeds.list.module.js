/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/list/calendar-feeds-list.component',
  'leave-absences/calendar-feeds/calendar-feeds.core'
], function (angular, listComponent) {
  return angular.module('calendar-feeds.list', [
    'calendar-feeds.core'
  ])
    .component(listComponent.__name, listComponent);
});
