/* eslint-env amd */

define([
  'leave-absences/shared/modules/models'
], function (models) {
  'use strict';

  return models.factory('CalendarFeed', [
    'Model', 'CalendarFeedAPI', 'CalendarFeedInstance',
    function (Model, CalendarFeedAPI, CalendarFeedInstance) {
      return Model.extend({
        all: all
      });

      /**
       * Get all feeds
       *
       * @return {Promise} resolves with an array of feeds instances
       */
      function all () {
        return CalendarFeedAPI.all()
          .then(function (response) {
            return response.map(function (calendarFeed) {
              return CalendarFeedInstance.init(calendarFeed, true);
            });
          });
      }
    }
  ]);
});
