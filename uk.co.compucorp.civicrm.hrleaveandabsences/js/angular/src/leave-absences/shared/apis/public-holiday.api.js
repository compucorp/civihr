define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('PublicHolidayAPI', ['$log', 'api', function ($log, api) {
    $log.debug('PublicHolidayAPI');

    return api.extend({
      /**
       * This method returns all the PublicHolidays.
       *
       * @param  {Object} params  matches the api endpoint params (title, date etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('PublicHolidayAPI');

        return this.sendGET('PublicHoliday', 'get', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
