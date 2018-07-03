/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  CalendarFeedAPI.__name = 'CalendarFeedAPI';
  CalendarFeedAPI.$inject = ['$log', '$q', 'api'];

  function CalendarFeedAPI ($log, $q, api) {
    $log.debug('CalendarFeedAPI');

    return api.extend({
      all: all
    });

    /**
     * Returns all Calendar Feeds
     *
     * @return {Promise} resolved with an array of objects of feeds
     */
    function all () {
      return this.sendGET('LeaveRequestCalendarFeedConfig', 'get', {}, false)
        .then(function (response) {
          return response.values;
        });
    }
  }

  return CalendarFeedAPI;
});
