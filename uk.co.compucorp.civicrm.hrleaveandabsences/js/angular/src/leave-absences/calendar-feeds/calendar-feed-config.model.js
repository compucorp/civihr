/* eslint-env amd */

define(function () {
  'use strict';

  CalendarFeedConfig.__name = 'CalendarFeedConfig';
  CalendarFeedConfig.$inject = ['Model', 'CalendarFeedConfigAPI', 'CalendarFeedConfigInstance'];

  return CalendarFeedConfig;

  function CalendarFeedConfig (Model, CalendarFeedConfigAPI, CalendarFeedConfigInstance) {
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
});
