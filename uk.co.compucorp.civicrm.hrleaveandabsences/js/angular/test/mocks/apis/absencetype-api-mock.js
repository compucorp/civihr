define([
  'mocks/module',
  'mocks/data/absencetype-data',
  'common/angularMocks',
], function (mocks, mockData) {
  'use strict';

  mocks.factory('AbsenceTypeAPIMock', ['$q', function ($q) {
    return {
      all: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.all().values);
        });
      }
    }
  }]);
});
