/* eslint-env amd */

define([
  'common/angular',
  'access-rights/services/apis/rights.api',
  'access-rights/services/models/location.model',
  'access-rights/services/models/region.model',
  'access-rights/services/models/right.model',
  'common/services/api',
  'common/models/model'
], function (angular, RightsAPI, Location, Region, Right) {
  'use strict';

  return angular.module('access-rights.models', [
    'common.apis',
    'common.models'
  ])
    .factory(RightsAPI)
    .factory(Location)
    .factory(Region)
    .factory(Right);
});
