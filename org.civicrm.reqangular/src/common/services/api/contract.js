/* eslint-env amd */

define([
  'common/modules/apis',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('api.contract', ['$log', 'api', function ($log, api) {
    $log.debug('api.contract');

    return api.extend({

      /**
       * This method returns all the contracts.
       *
       * It chains an additional call to the `getfulldetails` endpoint to also return
       * full details on contracts
       *
       * @param  {Object} params  matches the api endpoint params (ex. contact_id)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('ContractAPI.all');

        params = params || {};

        params['api.HRJobContract.getfulldetails'] = {'jobcontract_id': '$value.id'};
        params['deleted'] = 0;

        return this.sendGET('HRJobContract', 'get', params, false)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
