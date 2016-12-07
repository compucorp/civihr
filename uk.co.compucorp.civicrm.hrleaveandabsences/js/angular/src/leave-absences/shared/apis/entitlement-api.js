define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('EntitlementAPI', ['$log', 'api', function ($log, api) {
    $log.debug('EntitlementAPI');

    return api.extend({

      /**
       * This method returns all the entitlements (no pagination required), but optionally
       * it can chain `Entitlement.remainder` (PCHR-1132) to return also the balance (current and future)
       * among the rest of the data.
       *
       * If chaining, it should normalize the result by removing the `Entitlement.balance` property
       * from the result, so that the Model doesn't have to use a property that is specific to the
       * current API implementation
       *
       * @param  {Object} params      matches the api endpoint params (period_id, contact_id, etc)
       * @param  {boolean} withRemainder
       * @return {Promise}
       */
      all: function (params, withRemainder) {
        $log.debug('api.leave-absences.entitlement.all');

        var params = {};
        /*params = {
          sequential: 1
        }*/

        if (withRemainder) {
          params['api.LeavePeriodEntitlement.getremainder'] = {
            "entitlement_id": "$value.id",
            "include_future": true
          }
        }

        return this.sendGET('LeavePeriodEntitlement', 'get', params)
          .then(function (data) { return data.values; })
          .then(function (entitlements) {
            if (withRemainder) {
              /* normalize the results
              {
                id: '1',
                contract_id: '2',
                type_id: '4',
                //...
                balance: {
                  current: 20,
                  future: 10
                }
              }
              */
              //the data will have key 'api.LeavePeriodEntitlement.getremainder'
              _.map(entitlements, function(entitlement){
                //get array of values for remainder
                var remainder_values = entitlement['api.LeavePeriodEntitlement.getremainder']['values'];
                //this entitlement will have only one value so obtain that
                if(remainder_values.length === 1) {
                  entitlement['remainder'] = remainder_values[0]['remainder'];
                }
                //remove the chained key
                delete entitlement['api.LeavePeriodEntitlement.getremainder'];
                return entitlement;
              })
            }
            return entitlements;
          });
      },
      /**
       * This method returns all the leave balance changes of entitlement.
       *
       * @param  {Object} params      matches the api endpoint params (period_id, contact_id, etc)
       * @return {Promise}
       */
      breakdown: function (params) {
        $log.debug('api.leave-absences.entitlement.breakdown');

        return this.sendGET('LeavePeriodEntitlement', 'getbreakdown', params)
            .then(function (data) {
                return data.values;
            });
      }
    });
  }]);
});
