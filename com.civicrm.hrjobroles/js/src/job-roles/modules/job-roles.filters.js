/* eslint-env amd */

define([
  'common/angular',
  'job-roles/filters/get-active-values.filter'
], function (angular, getActiveValues) {
  'use strict';

  return angular.module('hrjobroles.filters', [])
    .filter(getActiveValues.__name, getActiveValues);
});
