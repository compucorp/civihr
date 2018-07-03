/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/calendar-feed.model',
  'common/models/model',
  'leave-absences/calendar-feeds/calendar-feeds.apis'
], function (angular, CalendarFeed) {
  return angular.module('calendar-feeds.models', [
    'common.models',
    'calendar-feeds.apis'
  ])
    .factory(CalendarFeed.__name, CalendarFeed);
});
