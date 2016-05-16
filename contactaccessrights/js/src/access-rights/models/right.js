define([
  'access-rights/modules/models',
  'access-rights/services/api/right',
  'common/services/api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Right', ['Model', 'rightApi', function (Model, api) {
    return Model.extend({
      getLocations: api.getLocations.bind(api),
      getRegions: api.getRegions.bind(api),
      deleteByIds: api.deleteByIds.bind(api),
      saveRegions: api.saveRegions.bind(api),
      saveLocations: api.saveLocations.bind(api)
    });
  }]);
});
