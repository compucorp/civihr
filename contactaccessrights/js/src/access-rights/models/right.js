define([
  'access-rights/modules/models',
  'common/services/api/api-builder'
], function (models) {
  'use strict';

  models.factory('Right', ['apiBuilder', '$q', '$location', function (apiBuilder, $q, $location) {
    var methods = {
      getLocations: function (filters, pagination, sort) {
        return this.getAllEntities(filters, pagination, sort, {
          action: 'getlocations'
        });
      },
      getRegions: function (filters, pagination, sort) {
        return this.getAllEntities(filters, pagination, sort, {
          action: 'getregions'
        });
      },
      deleteByIds: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.removeEntity({
            'id': id
          });
        }.bind(this)));
      },
      saveRegions: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.saveEntity({
            'entity_id': id,
            'entity_type': 'hrjc_region'
          });
        }.bind(this)));
      },
      saveLocations: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.saveEntity({
            'entity_id': id,
            'entity_type': 'hrjc_location'
          });
        }.bind(this)));
      }
    };
    return apiBuilder.build(methods, 'Rights', {
      'contact_id': $location.search()
        .cid
    });
  }]);
});
