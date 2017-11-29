/* eslint-env amd */

define([
  'common/angular',
  'common/services/api',
  'access-rights/apis/right.api'
], function (angular, __, rightApi) {
  'use strict';

  return angular.module('access-rights.apis', ['common.apis'])
    .factory(rightApi.__name, rightApi);
});
