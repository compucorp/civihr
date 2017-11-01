/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/apis',
  'common/services/api'
], function (_, apis) {
  'use strict';

  apis.factory('api.settings', ['$log', 'api', function ($log, api) {
    $log.debug('api.settings');

    return api.extend({
      /**
       * Returns all settings
       *
       * @param  {Object} params matches the api endpoint params (ex. contact_id)
       * @return {Promise} resolves settings data from backend as array of objects
       */
      get: function (params) {
        $log.debug('api.settings.get');

        params = params || {};

        return api.sendGET('Setting', 'get', params, false)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
