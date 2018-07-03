/* eslint-env amd */

define(function (models) {
  'use strict';

  CalendarFeed.__name = 'CalendarFeed';
  CalendarFeed.$inject = ['Model', 'CalendarFeedAPI', 'CalendarFeedInstance'];

  function CalendarFeed (Model, CalendarFeedAPI, CalendarFeedInstance) {
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

  return CalendarFeed;
});
