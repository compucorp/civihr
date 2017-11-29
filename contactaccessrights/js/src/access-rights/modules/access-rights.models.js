/* eslint-env amd */

define([
  'common/angular',
  'access-rights/models/location.model',
  'access-rights/models/region.model',
  'access-rights/models/right.model',
  'common/models/model',
  'access-rights/modules/access-rights.apis'
], function (angular, Location, Region, Right) {
  'use strict';

  return angular.module('access-rights.models', [
    'common.models',
    'access-rights.apis'
  ])
  .factory(Location.__name, Location)
  .factory(Region.__name, Region)
  .factory(Right.__name, Right);
});
