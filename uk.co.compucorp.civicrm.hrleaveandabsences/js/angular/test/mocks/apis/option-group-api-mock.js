define([
  'mocks/module',
  'mocks/data/option-group-mock-data',
  'common/angularMocks',
], function (mocks, mockData) {
  'use strict';

  mocks.factory('OptionGroupAPIMock', ['$q', function ($q) {
    return {
      valuesOf: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.getCollection(params));
        });
      }
    }
  }]);
});
