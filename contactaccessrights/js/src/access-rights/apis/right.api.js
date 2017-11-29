/* eslint-env amd */

define(function () {
  'use strict';

  rightApi.__name = 'rightApi';
  rightApi.$inject = ['api', '$q', '$location'];

  function rightApi (api, $q, $location) {
    var entityName = 'Rights';
    var additionalParams = {
      'contact_id': $location.search().cid
    };
    return api.extend({
      getLocations: function () {
        return this.sendGET(entityName, 'getlocations', additionalParams, false);
      },
      getRegions: function () {
        return this.sendGET(entityName, 'getregions', additionalParams, false);
      },
      deleteByIds: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'delete', {
            'contact_id': $location.search().cid,
            'id': id
          });
        }.bind(this)));
      },
      saveRegions: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'create', {
            'contact_id': $location.search().cid,
            'entity_id': id,
            'entity_type': 'hrjc_region'
          });
        }.bind(this)));
      },
      saveLocations: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'create', {
            'contact_id': $location.search().cid,
            'entity_id': id,
            'entity_type': 'hrjc_location'
          });
        }.bind(this)));
      }
    });
  }

  return rightApi;
});
