/* eslint-env amd */

define([
  'common/angular',
  '../calendar-feed-config.api',
  '../calendar-feed-config.instance',
  '../calendar-feed-config.model',
  './list.component',
  'common/models/instances/instance',
  'common/models/model',
  'common/services/api',
  'leave-absences/shared/modules/shared-settings'
], function (angular, calendarFeedConfigAPI, calendarFeedConfigInstance, calendarFeedConfig,
  listComponent) {
  return angular.module('calendar-feeds.list', [
    'common.apis',
    'common.models.instances',
    'common.models',
    'leave-absences.settings'
  ])
    .factory(calendarFeedConfigAPI.__name, calendarFeedConfigAPI)
    .factory(calendarFeedConfigInstance.__name, calendarFeedConfigInstance)
    .factory(calendarFeedConfig.__name, calendarFeedConfig)
    .component(listComponent.__name, listComponent);
});
