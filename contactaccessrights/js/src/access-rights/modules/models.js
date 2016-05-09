define([
  'common/angular',
  'common/modules/apis'
], function (angular) {
  'use strict';

  return angular.module('access-rights.models', ['common.apis', 'common.models']);
});
