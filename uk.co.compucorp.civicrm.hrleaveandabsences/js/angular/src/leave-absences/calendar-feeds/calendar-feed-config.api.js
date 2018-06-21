/* eslint-env amd */

define(function (_) {
  'use strict';

  CalendarFeedConfigAPI.__name = 'CalendarFeedConfigAPI';
  CalendarFeedConfigAPI.$inject = ['$log', '$q', 'api'];

  return CalendarFeedConfigAPI;

  function CalendarFeedConfigAPI ($log, $q, api) {
    $log.debug('CalendarFeedConfigAPI');

    return api.extend({
      all: all,
      create: create
    });

    /**
     * Returns all Calendar Feed Configs
     *
     * @return {Promise} resolved with an array of objects of feeds
     */
    function all () {
      return this.sendGET('LeaveRequestCalendarFeedConfig', 'get', {}, false)
        .then(function (response) {
          return response.values;
        });
    }

    /**
     * Create a new Calendar Feed Config
     *
     * @param  {Object} params
     *   params.title    {String}
     *   params.timezone {String} eg. "Europe/London"
     * @return {Promise} resolved with an object of a newly created feed
     */
    function create (params) {
      return this.sendPOST('LeaveRequestCalendarFeedConfig', 'create', params)
        .then(function (response) {
          return response.values;
        });
    }
  }
});
