/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/models/calendar-feeds.calendar-feed.model',
  'leave-absences/calendar-feeds/apis/calendar-feeds.calendar-feed.api',
  'leave-absences/calendar-feeds/instances/calendar-feeds.calendar-feed.instance',
  'common/models/model',
  'leave-absences/calendar-feeds/calendar-feeds.core'
], function (angular, CalendarFeedsCalendarFeed) {
  return angular.module('calendar-feeds.models', [
    'calendar-feeds.core',
    'common.models'
  ])
    .factory(CalendarFeedsCalendarFeed.__name, CalendarFeedsCalendarFeed);
});
