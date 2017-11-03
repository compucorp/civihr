/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models',
  'common/models/model',
  'leave-absences/shared/apis/entitlement.api',
  'leave-absences/shared/apis/entitlement-log.api',
  'leave-absences/shared/instances/entitlement.instance'
], function (_, models) {
  'use strict';

  models.factory('Entitlement', [
    '$log', 'Model', 'EntitlementAPI', 'EntitlementInstance',
    'EntitlementLogAPI',
    function ($log, Model, entitlementAPI, instance, entitlementLogAPI) {
      $log.debug('Entitlement');

      return Model.extend({
        /**
         * Calls the all() method of the Entitlement API, and returns an
         * EntitlementInstance for each entitlement.
         * It can pass the withRemainder property to get remainder for entitlements.
         *
         * @param {Object} params matches the api endpoint params (period_id, contact_id, etc)
         * @param {boolean} withRemainder to return remainder of data
         * @return {Promise}
         */
        all: function (params, withRemainder) {
          return entitlementAPI.all(this.processFilters(params), withRemainder)
            .then(function (entitlements) {
              return entitlements.map(function (entitlement) {
                return instance.init(entitlement, true);
              });
            });
        },
        /**
         * Calls the `breakdown` method of the entitlement API
         *
         * The return value of the promise changes based on whether an array of `EntitlementInstance`s
         * has been passed to the method or not.
         *
         * If it hasn 't, then it returns the entitlements along with breakdown details based on params passed.
         * If it has, then it loads the breakdown data into each correspondent entitlement, which then
         * are returned back.
         *
         * @param {Object} params matches the api endpoint params (period_id, contact_id, etc)
         * @param {Array}  [entitlements] an array of `EntitlementInstance`s
         * @return {Promise}
         */
        breakdown: function (params, entitlements) {
          return entitlementAPI.breakdown(params)
            .then(function (breakdown) {
              if (entitlements) {
                entitlements.map(function (entitlement) {
                  var foundEntitlement = _.find(breakdown, function (element) {
                    return element.id === entitlement.id;
                  });

                  if (foundEntitlement) {
                    entitlement['breakdown'] = foundEntitlement['breakdown'];
                  }

                  return entitlement;
                });

                return entitlements;
              }

              return breakdown.map(function (entitlement) {
                return instance.init(entitlement, true);
              });
            });
        },

        /**
         * Returns all Leave Entitlement Log entries. Can filter by contact and
         * period id.
         *
         * @param {Object} param - list of filters to pass to the api call.
         * @return {Promise} - resolves to a list of entitlement log entries.
         */
        logs: function (params) {
          params = _.clone(params || {});

          if (params.contact_id) {
            params['entitlement_id.contact_id'] = params.contact_id;
            delete params.contact_id;
          }

          if (params.period_id) {
            params['entitlement_id.period_id'] = params.period_id;
            delete params.period_id;
          }

          params.return = params.return || [];
          params.return.push('entitlement_id', 'entitlement_id.type_id',
            'editor_id', 'entitlement_amount', 'comment', 'created_date');

          return entitlementLogAPI.all(params);
        }
      });
    }
  ]);
});
