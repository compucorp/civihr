define([
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('EntitlementInstance', ['ModelInstance', function (ModelInstance) {

    return ModelInstance.extend({
      /**
       * Returns the default custom data (as in, not given by the Entitlement API)
       * with its default values
       *
       * @return {object}
       */
      defaultCustomData: function () {

        return {
          remainder: {
            current: 0,
            future: 0
          },
          breakdown: []
        }
      },

      /**
       * Populates the breakdown of the entitlement, by passing to the api
       * the entitlement id.
       *
       * @return {Promise} with updated entitlement model instance
       */
      breakdown: function () {

        return entitlementAPI.breakdown({
            entitlement_id: this.id
          })
          .then(function (breakdown) {
            this.breakdown = breakdown;
          }.bind(this));
      }
    });
  }]);
});
