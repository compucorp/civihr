/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/data/contract-data'
], function (_, mocks, mockData) {
  'use strict';

  mocks.factory('api.contract.mock', ['$q', function ($q) {
    return {
      all: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.all().values);
        });
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
