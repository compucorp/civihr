/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/services/models/calendar-feed.model',
  'leave-absences/calendar-feeds/services/models/calendar-feed.instance',
  'common/models/model',
  'leave-absences/calendar-feeds/calendar-feeds.apis'
], function (angular, CalendarFeed, CalendarFeedInstance) {
  return angular.module('calendar-feeds.models', [
    'common.models',
    'calendar-feeds.apis'
  ])
    .factory(CalendarFeed.__name, CalendarFeed)
    .factory(CalendarFeedInstance.__name, CalendarFeedInstance);
});
