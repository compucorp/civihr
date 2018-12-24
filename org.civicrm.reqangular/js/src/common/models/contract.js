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

      var model = Model.extend({
        all: all,
        activeForContact: activeForContact
      });

      /**
       * Fetches active job contracts for a contact
       *
       * @NOTE active contracts include future contracts as well
       *
       * @NOTE we cannot fetch by `is_current` because it is a virtual property
       * so we need to fetch all and then filter by `is_current` on front-end.
       *
       * @param  {String} contactId
       * @return {Promise} resolves with {JobContractInstance}
       */
      function activeForContact (contactId) {
        return this.all({ contact_id: contactId })
          .then(function (contracts) {
            return _.filter(contracts, { is_current: '1' });
          });
      }

      /**
       * Fetches all job contracts matching the search criteria
       *
       * @param  {Object} [params] matches API params (title, weight etc)
       * @return {Promise} resolves with [{ContractInstance}, ...]
       */
      function all (params) {
        return ContractAPI.all(params)
          .then(function (contracts) {
            return contracts.map(function (contract) {
              return instance.init(contract, true);
            });
          });
      }

      return model;
    }
  ]);
});
