define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('api.job-role', ['$log', 'api', function ($log, api) {
    $log.debug('api.jobRole');

    return api.extend({

      all: function (filters, pagination, sort, additionalParams) {
        $log.debug('api.jobRole.api');

        return this.getAll('HRJobRole', filters, pagination, sort, additionalParams);
      },

      find: function (id) {
        $log.debug('api.jobRole.find');

        return this.sendGET('HRJobRole', 'get', { id: '' + id }, false)
          .then(function (data) {
            return data.values[0];
          });
      }
    });
  }]);
});
