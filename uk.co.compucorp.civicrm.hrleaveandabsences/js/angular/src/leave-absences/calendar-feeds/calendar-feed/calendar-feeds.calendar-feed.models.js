/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/calendar-feed/calendar-feeds-calendar-feed.model',
  'common/models/model'
], function (angular, CalendarFeed) {
  return angular.module('calendar-feeds.models', [
    'common.models'
  ])
    .factory(CalendarFeed.__name, CalendarFeed);
});
