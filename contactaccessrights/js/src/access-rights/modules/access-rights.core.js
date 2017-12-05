/* eslint-env amd */

define([
  'common/angular',
  'common/ui-select',
  'common/angularBootstrap',
  'common/modules/xeditable-civi',
  'common/directives/loading'
], function (angular) {
  'use strict';

  angular.module('access-rights.core', [
    'ngAnimate',
    'ui.bootstrap',
    'ui.select',
    'xeditable-civi',
    'common.directives'
  ]);
});
