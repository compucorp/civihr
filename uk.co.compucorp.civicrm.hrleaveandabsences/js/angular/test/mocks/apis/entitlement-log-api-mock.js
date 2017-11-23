/* eslint-env amd, jasmine */

define([
  'mocks/module',
  'mocks/data/entitlement-log-data',
  'common/angularMocks'
], function (mocks, mockData) {
  'use strict';

  mocks.factory('EntitlementLogAPIMock', ['$q', function ($q) {
    return {
      all: function (params) {
        return $q.resolve(mockData.all().values);
      }
    };
  }]);
});
