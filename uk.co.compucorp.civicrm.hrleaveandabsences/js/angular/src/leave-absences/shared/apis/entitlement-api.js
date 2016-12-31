define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('EntitlementAPI', ['$log', 'api', function ($log, api) {
    $log.debug('EntitlementAPI');

    return api.extend({

      /**
       * This method returns all the entitlements.
       * It can also return the remainder (current and future) among the rest of
       * the data when passed withRemainder.
       *
       * @param  {Object} params  matches the api endpoint params (period_id, contact_id, etc)
       * @param  {boolean} withRemainder  can be set to true to return remainder of entitlements
       * @return {Promise}
       */
      all: function (params, withRemainder) {
        $log.debug('EntitlementAPI.all');

        var params = {};

        if (withRemainder) {
          params['api.LeavePeriodEntitlement.getremainder'] = {
            'entitlement_id': '$value.id',
            'include_future': true
          }
        }

        return this.sendGET('LeavePeriodEntitlement', 'get', params)
          .then(function (data) {
            return data.values;
          })
          .then(function (entitlements) {
            if (withRemainder) {
              //entitlements data will have key 'api.LeavePeriodEntitlement.getremainder'
              //which is normalized with a friendlier 'remainder' key
              entitlements.map(function (entitlement) {
                var remainderValues = entitlement['api.LeavePeriodEntitlement.getremainder']['values'];
                if (remainderValues.length) {
                  entitlement['remainder'] = remainderValues[0]['remainder'];
                }
                delete entitlement['api.LeavePeriodEntitlement.getremainder'];

                return entitlement;
              });
            }
            return entitlements;
          });
      },
      /**
       * This method returns the breakdown of entitlement from various types of leave balances.
       *
       * @param  {Object} params  matches the api endpoint params (period_id, contact_id, etc)
       * @return {Promise}  will return a promise which when resolved will contain breakdown
       * details along with entitlement id
       */
      breakdown: function (params) {
        $log.debug('EntitlementAPI.breakdown');

        return this.sendGET('LeavePeriodEntitlement', 'getbreakdown', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
