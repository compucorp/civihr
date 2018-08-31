/* eslint-env amd */

define([
  'common/angular',
  'access-rights/apis/rights.api',
  'access-rights/models/location.model',
  'access-rights/models/region.model',
  'access-rights/models/right.model',
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
