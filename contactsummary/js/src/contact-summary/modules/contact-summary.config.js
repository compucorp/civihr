/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('contactsummary.config', ['contactsummary.constants']).config(config);

  config.$inject = [
    'settings', '$routeProvider', '$resourceProvider', '$httpProvider',
    '$logProvider', '$urlRouterProvider', '$stateProvider'
  ];

  function config (settings, $routeProvider, $resourceProvider, $httpProvider,
    $logProvider, $urlRouterProvider, $stateProvider) {
    $logProvider.debugEnabled(settings.debug);

    $urlRouterProvider.otherwise('/');
    $stateProvider
      .state('contact-summary', {
        url: '/',
        controller: 'ContactSummaryController',
        controllerAs: 'ContactSummaryCtrl',
        templateUrl: settings.pathBaseUrl + settings.pathTpl + 'mainTemplate.html'
      });

    $resourceProvider.defaults.stripTrailingSlashes = false;

    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  }
});
