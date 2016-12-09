//intercepts paths for real APIs and returns mock data
define([
  'mocks/module',
  'mocks/data/entitlement-data',
  'common/angularMocks',
], function (mocks, mockData) {
  'use strict';

  mocks.factory('EntitlementAPIMock', ['$q', function ($q) {

    return {
      all: function (params, withBalance) {
        if (withBalance) {
          return $q(function (resolve, reject) {
            resolve(mockData.all({}, true).values);
          });
        }
        return $q(function (resolve, reject) {
          resolve(mockData.all().values);
        });
      },
      breakdown: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.breakdown().values);
        });
      }
    }
  }]);
});