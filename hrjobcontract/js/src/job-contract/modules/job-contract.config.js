/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('job-contract.config', ['job-contract.constants']).config(config);

  config.$inject = [
    'settings', '$routeProvider', '$resourceProvider', '$logProvider',
    '$httpProvider', 'uibDatepickerConfig', 'uiSelectConfig'
  ];

  function config (settings, $routeProvider, $resourceProvider, $logProvider,
    $httpProvider, datepickerConfig, uiSelectConfig) {
    $logProvider.debugEnabled(settings.debug);

    $routeProvider
      .resolveForAll({
        format: ['DateFormat', function (DateFormat) {
          return DateFormat.getDateFormat();
        }]
      })
      .when('/', {
        controller: 'ContractListCtrl',
        templateUrl: settings.pathApp + 'views/contractList.html',
        resolve: {
          contractList: ['ContractService', function (ContractService) {
            return ContractService.get();
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
