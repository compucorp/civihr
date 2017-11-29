/* eslint-env amd */

define(function () {
  'use strict';

  Right.__name = 'Right';
  Right.$inject = ['Model', 'rightApi'];

  function Right (Model, api) {
    return Model.extend({
      getLocations: api.getLocations.bind(api),
      getRegions: api.getRegions.bind(api),
      deleteByIds: api.deleteByIds.bind(api),
      saveRegions: api.saveRegions.bind(api),
      saveLocations: api.saveLocations.bind(api)
    });
  }

  return Right;
});
