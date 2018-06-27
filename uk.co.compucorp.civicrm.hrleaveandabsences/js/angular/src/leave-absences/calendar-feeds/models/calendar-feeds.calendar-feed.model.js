/* eslint-env amd */

define([
  'leave-absences/shared/modules/models',
  'common/models/model',
  'leave-absences/calendar-feeds/apis/calendar-feeds.calendar-feed.api',
  'leave-absences/calendar-feeds/instances/calendar-feeds.calendar-feed.instance'
], function (models) {
  'use strict';

  models.factory('CalendarFeedConfig', [
    'Model', 'CalendarFeedConfigAPI', 'CalendarFeedConfigInstance',
    function (Model, CalendarFeedConfigAPI, CalendarFeedConfigInstance) {
      return Model.extend({
        all: all
      });

      /**
       * Get all feeds
       *
       * @return {Promise} resolves with an array of feeds instances
       */
      function all () {
        return CalendarFeedConfigAPI.all()
          .then(function (response) {
            return response.map(function (calendarFeedConfig) {
              return CalendarFeedConfigInstance.init(calendarFeedConfig, true);
            });
          });
      }
    }
  ]);
});
