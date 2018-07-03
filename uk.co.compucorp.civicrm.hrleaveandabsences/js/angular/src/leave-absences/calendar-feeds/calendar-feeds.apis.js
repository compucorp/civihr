/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds-calendar-feed.api',
  'common/services/api'
], function (angular, CalendarFeedAPI) {
  return angular.module('calendar-feeds.apis', [
    'common.apis'
  ])
    .factory(CalendarFeedAPI.__name, CalendarFeedAPI);
});
