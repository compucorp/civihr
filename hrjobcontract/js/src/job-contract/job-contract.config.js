/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('job-contract.config', ['job-contract.constants']).config(config);

  config.$inject = [
    '$httpProvider', '$logProvider', '$resourceProvider', '$routeProvider',
    'uibDatepickerConfig', 'uiSelectConfig', 'settings'
  ];

  function config ($httpProvider, $logProvider, $resourceProvider, $routeProvider,
    datepickerConfig, uiSelectConfig, settings) {
    $logProvider.debugEnabled(settings.debug);

    $routeProvider
      .resolveForAll({
        format: ['DateFormat', function (DateFormat) {
          return DateFormat.getDateFormat();
        }]
      })
      .when('/', {
        controller: 'ContractListController',
        templateUrl: settings.baseUrl + 'controllers/contract-list.html',
        resolve: {
          contractList: ['contractService', function (contractService) {
            return contractService.get();
          }]
        }
      }
      )
      .otherwise({ redirectTo: '/' });

    $resourceProvider.defaults.stripTrailingSlashes = false;
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    uiSelectConfig.theme = 'bootstrap';
    datepickerConfig.showWeeks = false;
  }
});
