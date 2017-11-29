/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('access-rights.config', []).config(config);

  config.$inject = ['$locationProvider', '$httpProvider'];

  function config ($locationProvider, $httpProvider) {
    $locationProvider.html5Mode({
      enabled: true,
      requireBase: false
    });

    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  }
});
