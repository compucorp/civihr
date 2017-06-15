/* eslint-env amd */

define([
  'common/modules/models-instances',
  'common/models/instances/instance',
  'common/services/api/contract'
], function (instances) {
  'use strict';

  instances.factory('ContractInstance', ['ModelInstance', 'api.contract',
    function (ModelInstance, ContractAPI) {
      return ModelInstance.extend({

        /**
         * Gets all existing (non-deleted) contracts
         * @param {Object} params - any additional parameters
         * @return {Promise}
         */
        all: function (params) {
          return ContractAPI.all(params);
        }
      });
    }]);
});
