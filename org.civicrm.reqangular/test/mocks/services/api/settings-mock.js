/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/data/settings.data'
], function (_, mocks, SettingsData) {
  'use strict';

  mocks.factory('api.settings.mock', ['$q', function ($q) {
    return {
      get: function (params) {
        return $q(function (resolve, reject) {
          resolve(SettingsData.get.values);
        });
      },

      /**
       * Returns mocked settings
       *
       * @return {object}
       */
      mockedSettings: function () {
        return SettingsData.get.values;
      },

      /**
       * Adds a spy on every method for testing purposes
       */
      spyOnMethods: function () {
        _.functions(this).forEach(function (method) {
          spyOn(this, method).and.callThrough();
        }.bind(this));
      }
    };
  }]);
});
