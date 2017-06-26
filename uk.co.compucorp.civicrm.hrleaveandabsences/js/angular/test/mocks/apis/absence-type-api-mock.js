/* eslint-env amd, jasmine */

define([
  'mocks/module',
  'mocks/data/absence-type-data',
  'common/angularMocks'
], function (mocks, mockData) {
  'use strict';

  mocks.factory('AbsenceTypeAPIMock', ['$q', function ($q) {
    return {
      all: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.all().values);
        });
      },
      calculateToilExpiryDate: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.calculateToilExpiryDate().values.expiry_date);
        });
      }
    };
  }]);
});
