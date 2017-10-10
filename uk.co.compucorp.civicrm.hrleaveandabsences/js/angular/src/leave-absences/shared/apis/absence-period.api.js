/* eslint-env amd */

define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('AbsencePeriodAPI', ['$log', 'api', function ($log, api) {
    $log.debug('AbsencePeriodAPI');

    return api.extend({
      /**
       * This method returns all the AbsencePeriods.
       *
       * @param  {Object} params  matches the api endpoint params (title, start_date, end_date etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('AbsencePeriodAPI');

        return this.sendGET('AbsencePeriod', 'get', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
