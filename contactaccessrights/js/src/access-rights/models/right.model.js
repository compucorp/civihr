/* eslint-env amd */

define(function () {
  'use strict';

  Right.__name = 'Right';
  Right.$inject = ['Model', 'RightsAPI'];

  function Right (Model, RightsAPI) {
    return Model.extend({
      getLocations: RightsAPI.getLocations.bind(RightsAPI),
      getRegions: RightsAPI.getRegions.bind(RightsAPI),
      deleteByIds: RightsAPI.deleteByIds.bind(RightsAPI),
      saveRegions: RightsAPI.saveRegions.bind(RightsAPI),
      saveLocations: RightsAPI.saveLocations.bind(RightsAPI)
    });
  }

  return Right;
});
