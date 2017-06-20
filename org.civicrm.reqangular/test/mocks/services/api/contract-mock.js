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
          resolve(mockData.all().values.map(storeDetails));
        });
      },

      /**
       * Returns mocked contracts
       *
       * @return {object}
       */
      mockedContracts: function () {
        return mockData.all().values.map(storeDetails);
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

    /**
     * Contracts data will have key 'api.HRJobContract.getfulldetails'
     * which is normalized with a friendlier 'details' key
     *
     * @param  {Object} contract
     * @return {Object}
     */
    function storeDetails (contract) {
      var clone = _.clone(contract);

      clone.info = clone['api.HRJobContract.getfulldetails'];
      delete clone['api.HRJobContract.getfulldetails'];

      return clone;
    }
  }]);
});
