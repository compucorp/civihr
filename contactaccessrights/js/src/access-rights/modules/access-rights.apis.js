/* eslint-env amd */

define([
  'common/angular',
  'common/services/api',
  'access-rights/apis/rights.api'
], function (angular, __, RightsAPI) {
  'use strict';

  return angular.module('access-rights.apis', ['common.apis'])
    .factory(RightsAPI);
});
