/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/services/apis/calendar-feed.api',
  'leave-absences/calendar-feeds/services/models/calendar-feed.model',
  'leave-absences/calendar-feeds/services/models/calendar-feed.instance',
  'common/models/model',
  'common/services/api'
], function (angular, CalendarFeedAPI, CalendarFeed, CalendarFeedInstance) {
  return angular.module('calendar-feeds.models', [
    'common.apis',
    'common.models'
  ])
    .factory(CalendarFeedAPI.__name, CalendarFeedAPI)
    .factory(CalendarFeed.__name, CalendarFeed)
    .factory(CalendarFeedInstance.__name, CalendarFeedInstance);
});
