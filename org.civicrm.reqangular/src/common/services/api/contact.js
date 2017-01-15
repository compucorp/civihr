define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('api.contact', ['$log', 'api', function ($log, api) {
    $log.debug('api.contact');

    return api.extend({

      all: function (filters, pagination, sort, additionalParams) {
        $log.debug('api.contact.api');

        return this.getAll('Contact', filters, pagination, sort, additionalParams);
      },

      find: function (id) {
        $log.debug('api.contact.find');

        return this.sendGET('Contact', 'get', { id: '' + id }, false)
          .then(function (data) {
            return data.values[0];
          });
      }
    });
  }]);
});
