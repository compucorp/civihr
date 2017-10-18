/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/module',
  'mocks/data/entitlement-data',
  'mocks/data/leave-balance-report.data',
  'common/angularMocks'
], function (_, mocks, mockData, leaveBalanceReportMockData) {
  'use strict';

  mocks.factory('EntitlementAPIMock', ['$q', function ($q) {
    /**
     * A copy of part of the implementation of the real API
     *
     * TODO: This definitely should be improved, should be figured out how
     * to remove duplication between real and mocked api
     */
    function storeRemainder (entitlement) {
      var clone = _.clone(entitlement);
      var remainderValues = clone['api.LeavePeriodEntitlement.getremainder']['values'];

      if (remainderValues.length) {
        clone['remainder'] = remainderValues[0]['remainder'];
      }

      delete clone['api.LeavePeriodEntitlement.getremainder'];

      return clone;
    }

    /**
     * A copy of part of the implementation of the real API
     *
     * TODO: This definitely should be improved, should be figured out how
     * to remove duplication between real and mocked api
     */
    function storeValue (entitlement) {
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
            resolve(mockData.all({}, true).values.map(storeValue).map(storeRemainder));
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
      },
      getLeaveBalances: function (params) {
        var data = leaveBalanceReportMockData.all().values;

        return $q(function (resolve, reject) {
          resolve({
            list: data,
            allIds: [],
            total: data.length
          });
        });
      }
    };
  }]);
});
