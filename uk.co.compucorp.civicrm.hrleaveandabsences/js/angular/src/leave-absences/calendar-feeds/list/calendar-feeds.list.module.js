/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/list/calendar-feeds-display-link.directive',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.module'
], function (angular, DisplayLink) {
  return angular.module('calendar-feeds.list', [
    'calendar-feeds.link-modal'
  ])
    .directive(DisplayLink.__name, DisplayLink);
});
