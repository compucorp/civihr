/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/list/directives/calendar-feeds-display-link.directive',
  'leave-absences/calendar-feeds/list/list.core'
], function (angular, DisplayLink) {
  angular.module('calendar-feeds.list', [
    'calendar-feeds.list.core'
  ])
    .directive(DisplayLink.__name, DisplayLink);
});
