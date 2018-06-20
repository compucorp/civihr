/* eslint-env amd */

define([
  'leave-absences/shared/modules/apis',
  'common/lodash',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('CalendarFeedConfigAPI', ['$log', 'api', '$q',
    function ($log, api, $q) {
      $log.debug('CalendarFeedConfigAPI');

      var methods = {
        all: all,
        create: create
      };

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

      return api.extend(methods);
    }]);
});
