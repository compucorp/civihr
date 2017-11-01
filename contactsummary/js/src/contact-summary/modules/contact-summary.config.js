/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  return angular.module('contactsummary.config', ['contactsummary.constants']).config(['settings', '$routeProvider', '$resourceProvider', '$httpProvider', '$logProvider',
    function (settings, $routeProvider, $resourceProvider, $httpProvider, $logProvider) {
      $logProvider.debugEnabled(settings.debug);

      $routeProvider
        .when('/', {
          controller: 'ContactSummaryController',
          controllerAs: 'ContactSummaryCtrl',
          templateUrl: settings.pathBaseUrl + settings.pathTpl + 'mainTemplate.html',
          resolve: {}
        })
        .otherwise({redirectTo: '/'});

      $resourceProvider.defaults.stripTrailingSlashes = false;

      $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }
  ]);
});
