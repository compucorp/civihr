/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('hrjobroles.config', ['hrjobroles.constants']).config(hrJobRolesConfig);

  hrJobRolesConfig.$inject = [
    '$httpProvider', '$logProvider', '$resourceProvider', '$routeProvider',
    'settings'
  ];

  function hrJobRolesConfig ($httpProvider, $logProvider, $resourceProvider,
    $routeProvider, settings) {
    $logProvider.debugEnabled(settings.debug);

    $routeProvider
      .resolveForAll({
        format: ['DateFormat', function (DateFormat) {
          return DateFormat.getDateFormat();
        }]
      })
      .when('/', {
        templateUrl: settings.pathBaseUrl + settings.pathTpl + 'mainTemplate.html?v=1',
        resolve: {},
        controller: 'JobRolesController',
        controllerAs: 'jobroles'
      })
      .otherwise({ redirectTo: '/' });

    $resourceProvider.defaults.stripTrailingSlashes = false;
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  }
});
