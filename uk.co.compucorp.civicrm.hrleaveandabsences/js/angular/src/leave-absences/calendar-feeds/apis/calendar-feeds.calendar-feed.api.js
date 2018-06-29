/* eslint-env amd */

define([
  'leave-absences/shared/modules/apis',
  'common/lodash',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('CalendarFeedAPI', ['$log', 'api', '$q',
    function ($log, api, $q) {
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
    }]);
});
