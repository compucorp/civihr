/* eslint-env amd */

define([
  'common/modules/models',
  'common/models/model',
  'common/models/instances/settings-instance',
  'common/services/api/settings.api'
], function (models) {
  'use strict';

  models.factory('Settings', [
    '$log', 'Model', 'api.settings', 'SettingsInstance',
    function ($log, Model, SettingsAPI, instance) {
      $log.debug('Settings');

      var promise;

      return Model.extend({

        /**
         * Calls the get() method of the Settings API, and returns a
         * SettingsInstance for each setting.
         *
         * @param  {Object} params
         * @return {Promise}
         */
        get: function (params) {
          return SettingsAPI.get(params)
            .then(function (settings) {
              return settings.map(function (setting) {
                return instance.init(setting, true);
              });
            });
        },

        /**
         * Fetched the thousand and decimal seperators form backend
         *
         * @return {promise}
         */
        fetchSeparators: function () {
          promise = promise || this.get();

          return promise.then(function (result) {
            return {
              decimal: result[0].monetaryDecimalPoint,
              thousand: result[0].monetaryThousandSeparator
            };
          });
        }
      });
    }
  ]);
});
