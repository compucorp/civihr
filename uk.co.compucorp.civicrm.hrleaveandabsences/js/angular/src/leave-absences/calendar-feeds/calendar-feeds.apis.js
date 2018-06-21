/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/calendar-feed-config.api',
  'common/services/api'
], function (angular, calendarFeedConfigAPI) {
  return angular.module('calendar-feeds.apis', [
    'common.apis'
  ])
    .factory(calendarFeedConfigAPI.__name, calendarFeedConfigAPI);
});
