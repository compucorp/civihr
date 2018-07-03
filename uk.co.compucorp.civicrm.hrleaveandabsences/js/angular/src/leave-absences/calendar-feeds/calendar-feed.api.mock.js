/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/calendar-feeds/calendar-feed.api.data',
  'leave-absences/mocks/module',
  'common/angularMocks'
], function (_, calendarFeedData, mocks) {
  'use strict';

  mocks.factory('CalendarFeedAPIMock', [
    '$q',
    function ($q) {
      var methods = {
        all: all
      };

      /**
       * Returns mocked data for all() method
       *
       * @return {Promise} resolves with an array of feed objects
       */
      function all () {
        return $q.resolve()
          .then(function () {
            return calendarFeedData.all().values;
          });
      }

      return methods;
    }
  ]);
});
