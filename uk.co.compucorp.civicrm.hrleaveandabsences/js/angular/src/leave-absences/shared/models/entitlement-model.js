define([
  'leave-absences/shared/modules/models',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Entitlement', [
      '$log', 'Model', 'EntitlementAPI', 'EntitlementInstance',
      function ($log, Model, entitlementAPI, instance) {

        return Model.extend({
          /**
           * Calls the all() method of the API, and returns a EntitlementInstance for each entitlement.
           * It can pass the withBalance property (should default to true?)
           *
           * @param {Object} params matches the api endpoint params (period_id, contact_id, etc)
           * @param {boolean} withBalance
           * @return {Promise}
           */
          all: function(params, withBalance) {
            return entitlementAPI.all(params, withBalance)
              .then(function (entitlements) {
                return entitlements.map(function (entitlement) {
                  return instance.init(entitlement, true);
                });
              });
          },
          /**
           * Calls the `breakdown` method of the API
           *
           * The return value of the promise changes based whether an array of `EntitlementInstance`s
           * has been passed to the method or not.
           *
           * If it hasn't, then it simply return the API response.
           * If it has, then it loads the breakdown data into each corrispondent entitlement, which then
           * are returned back.
           *
           * @param {Object} params matches the api endpoint params (period_id, contact_id, etc)
           * @param {Array}  [entitlements] an array of `EntitlementInstance`s
           * @return {Promise}
           */
          breakdown: function(params, entitlements) {
            return entitlementAPI.breakdown(params)
              .then( function(breakdown) {
                if (entitlements) {
                  // load breakdown data in each of the `EntitlementInstance`s
                  entitlements.map( function( entitlement ){
                    var filtered = breakdown.filter( function(element){
                      element.id === entitlement.id;
                    });

                    //append breakdown data to entitlement
                    if(filtered.length > 0){
                      entitlement['breakdown'] = filtered[0]['breakdown'];
                    }
                    return entitlement;
                  });

                  return entitlements;
                } else {

                  return breakdown;
                }
              });
          }
        });
      }
  ]);
})
