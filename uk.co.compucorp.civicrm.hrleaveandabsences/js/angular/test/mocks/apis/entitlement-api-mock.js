//intercepts paths for real APIs and returns mock data
define([
  'mocks/module',
  'mocks/data/entitlement-data',
  'common/angularMocks',
], function (mocks, mockData) {
  'use strict';

  mocks.factory('EntitlementAPIMock', ['$q', function ($q) {

    /**
     * A copy of part of the implementation of the real API
     *
     * TODO: This definitely should be improved, should be figured out how
     * to remove duplication between real and mocked api
     */
    function storeValue(entitlement) {
      var clone = _.clone(entitlement);
      var value = clone['api.LeavePeriodEntitlement.getentitlement'].values[0].entitlement;

      clone['value'] = value;
      delete clone['api.LeavePeriodEntitlement.getentitlement'];

      return clone;
    }

    return {
      all: function (params, withBalance) {
        if (withBalance) {
          return $q(function (resolve, reject) {
            resolve(mockData.all({}, true).values.map(storeValue));
          });
        }
        return $q(function (resolve, reject) {
          resolve(mockData.all().values.map(storeValue));
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
