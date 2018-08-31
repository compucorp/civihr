/* eslint-env amd */

define(function () {
  'use strict';

  RightsAPI.$inject = ['api', '$q', 'beforeHashQueryParams'];

  function RightsAPI (api, $q, beforeHashQueryParams) {
    var entityName = 'Rights';
    var queryParams = beforeHashQueryParams.parse();
    var additionalParams = {
      'contact_id': queryParams.cid
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
            'contact_id': queryParams.cid,
            'id': id
          });
        }.bind(this)));
      },
      saveRegions: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'create', {
            'contact_id': queryParams.cid,
            'entity_id': id,
            'entity_type': 'hrjc_region'
          });
        }.bind(this)));
      },
      saveLocations: function (ids) {
        return $q.all(ids.map(function (id) {
          return this.sendPOST(entityName, 'create', {
            'contact_id': queryParams.cid,
            'entity_id': id,
            'entity_type': 'hrjc_location'
          });
        }.bind(this)));
      }
    });
  }

  return { RightsAPI: RightsAPI };
});
