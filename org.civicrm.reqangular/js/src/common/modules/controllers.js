define([
  'common/angular',
  'common/angularBootstrap',
  'common/modules/apis'
], function (angular) {
  'use strict';

  return angular.module('common.controllers', ['ui.bootstrap', 'common.apis']);
});
