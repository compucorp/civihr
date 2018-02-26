/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('access-rights.config', []).config(config);

  config.$inject = ['$httpProvider'];

  function config ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  }
});
