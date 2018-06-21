/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/calendar-feed-config.model',
  'leave-absences/calendar-feeds/calendar-feed-config.instance',
  'common/models/instances/instance',
  'common/models/model'
], function (angular, calendarFeedConfigInstance, calendarFeedConfig) {
  return angular.module('calendar-feeds.models', [
    'common.models',
    'common.models.instances'
  ])
    .factory(calendarFeedConfig.__name, calendarFeedConfig)
    .factory(calendarFeedConfigInstance.__name, calendarFeedConfigInstance);
});
