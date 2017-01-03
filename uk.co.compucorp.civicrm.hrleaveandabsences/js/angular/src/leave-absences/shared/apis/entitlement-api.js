define([
  'leave-absences/shared/modules/apis',
  'common/lodash',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('EntitlementAPI', ['$log', 'api', function ($log, api) {
    $log.debug('EntitlementAPI');

    /**
     * Entitlements data will have key 'api.LeavePeriodEntitlement.getremainder'
     * which is normalized with a friendlier 'remainder' key
     *
     * @param  {Object} entitlement
     * @return {Object}
     */
    function storeRemainder(entitlement) {
      var clone = _.clone(entitlement);
      var remainderValues = clone['api.LeavePeriodEntitlement.getremainder']['values'];

      if (remainderValues.length) {
        clone['remainder'] = remainderValues[0]['remainder'];
      }

      delete clone['api.LeavePeriodEntitlement.getremainder'];

      return clone;
    }

    /**
     * Entitlements data will have key 'api.LeavePeriodEntitlement.getentitlement'
     * which is normalized with a friendlier 'value' key
     *
     * @param  {Object} entitlement
     * @return {Object}
     */
    function storeValue(entitlement) {
      var clone = _.clone(entitlement);
      var value = clone['api.LeavePeriodEntitlement.getentitlement'].values[0].entitlement;

      clone['value'] = value;
      delete clone['api.LeavePeriodEntitlement.getentitlement'];

      return clone;
    }

    return api.extend({

      /**
       * This method returns all the entitlements.
       *
       * It chains an additional call to the `getentitlement` endpoint to also return
       * the actual value of each entitlement
       *
       * It can also return the remainder (current and future) among the rest of
       * the data when passed withRemainder.
       *
       * @param  {Object} params  matches the api endpoint params (period_id, contact_id, etc)
       * @param  {boolean} withRemainder  can be set to true to return remainder of entitlements
       * @return {Promise}
       */
      all: function (params, withRemainder) {
        $log.debug('EntitlementAPI.all');

        params['api.LeavePeriodEntitlement.getentitlement'] = {
          'entitlement_id': '$value.id'
        };

        if (withRemainder) {
          params['api.LeavePeriodEntitlement.getremainder'] = {
            'entitlement_id': '$value.id',
            'include_future': true
          }
        }

        return this.sendGET('LeavePeriodEntitlement', 'get', params, false)
          .then(function (data) {
            return data.values;
          })
          .then(function (entitlements) {
            entitlements = entitlements.map(storeValue);

            if (withRemainder) {
              entitlements = entitlements.map(storeRemainder);
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
