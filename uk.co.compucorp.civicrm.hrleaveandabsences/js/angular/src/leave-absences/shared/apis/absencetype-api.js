define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('AbsenceTypeAPI', ['$log', 'api', function ($log, api) {
    $log.debug('AbsenceTypeAPI');

    return api.extend({

      /**
       * This method returns all the AbsenceTypes.
       *
       * @param  {Object} params  matches the api endpoint params (title, weight etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('AbsenceTypeAPI');

        return this.sendGET('AbsenceType', 'get', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
