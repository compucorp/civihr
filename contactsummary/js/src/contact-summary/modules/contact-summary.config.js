/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('contactsummary.config', ['contactsummary.constants']).config(config);

  config.$inject = [
    'settings', '$routeProvider', '$resourceProvider', '$httpProvider',
    '$logProvider', '$urlServiceProvider', '$stateProvider'
  ];

  function config (settings, $routeProvider, $resourceProvider, $httpProvider,
    $logProvider, $urlServiceProvider, $stateProvider) {
    $logProvider.debugEnabled(settings.debug);
    $resourceProvider.defaults.stripTrailingSlashes = false;

    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  }
});
