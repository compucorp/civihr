define([
  'access-rights/modules/models',
  'common/services/api/api-builder'
], function (models) {
  'use strict';

  models.factory('api.right', ['api', '$q', '$location', function (api, $q, $location) {
    var entityName = 'Rights';
    var additionalParams = {
      'contact_id': $location.search().cid
    };

    var methods = {
      getLocations: function (filters, pagination, sort) {
        return this.sendGET(entityName, 'getlocations', additionalParams, false);
      },
      getRegions: function (filters, pagination, sort) {
        return this.sendGET(entityName, 'getregions', additionalParams, false);
      },
      deleteByIds: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'delete', _.assign(additionalParams, {
            'id': id
          }));
        }.bind(this)));
      },
      saveRegions: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'create', _.assign(additionalParams, {
            'entity_id': id,
            'entity_type': 'hrjc_region'
          }))
        }.bind(this)));
      },
      saveLocations: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'create', _.assign(additionalParams, {
            'entity_id': id,
            'entity_type': 'hrjc_location'
          }))
        }.bind(this)));
      }
    };
    return api.extend(methods);
  }]);
});
