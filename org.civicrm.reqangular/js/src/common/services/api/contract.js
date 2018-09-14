/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/apis',
  'common/services/api'
], function (_, apis) {
  'use strict';

  apis.factory('api.contract', ['$log', 'api', function ($log, api) {
    $log.debug('api.contract');

    return api.extend({

      /**
       * Returns all contracts.
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

        params['api.HRJobContract.getfulldetails'] = {
          'jobcontract_id': '$value.id'
        };
        /*
         * "deleted" is set to 0 as we are not supposed to get full details
         * of a deleted contract. It is not also allowed by API.
         */
        params['deleted'] = 0;

        return this.sendGET('HRJobContract', 'get', params, false)
          .then(function (data) {
            return data.values;
          }).then(function (contracts) {
            contracts = contracts.map(storeDetails);

            return contracts;
          });
      },
      /**
       * Gets IDs and display names of contacts who has job contracts for the given period
       *
       * @param  {String} startDate YYYY-MM-DD
       * @param  {String} endDate YYYY-MM-DD
       * @return {Promise} resolves to an {Array} collection of `id` and `display_name`
       */
      getContactsWithContractsInPeriod: function (startDate, endDate) {
        return this.sendGET('HRJobContract', 'getcontactswithcontractsinperiod', {
          start_date: startDate,
          end_date: endDate
        })
          .then(function (response) {
            return response.values;
          });
      }
    });

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
