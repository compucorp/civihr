/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/models',
  'common/models/model',
  'common/models/instances/contract-instance',
  'common/services/api/contract'
], function (_, models) {
  'use strict';

  models.factory('Contract', [
    '$log', 'Model', 'api.contract', 'ContractInstance',
    function ($log, Model, ContractAPI, instance) {
      $log.debug('Contract');

      return Model.extend({
        /**
         * Calls the all() method of the Contract API, and returns a
         * ContractInstance for each contract.
         *
         * @param  {Object} params  matches the api endpoint params (title, weight etc)
         * @return {Promise}
         */
        all: function (params) {
          return ContractAPI.all(params)
            .then(function (contracts) {
              return contracts.map(function (contract) {
                return instance.init(contract, true);
              });
            });
        }
      });
    }
  ]);
});
