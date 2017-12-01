/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('job-contract.run', ['job-contract.constants']).run(run);

  run.$inject = [
    '$log', '$q', '$rootScope', 'settings', 'ContractService', 'ContractDetailsService',
    'ContractHourService', 'ContractPayService', 'ContractLeaveService',
    'ContractHealthService', 'ContractPensionService'
  ];

  function run ($log, $q, $rootScope, settings, ContractService, ContractDetailsService,
    ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService,
    ContractPensionService) {
    $log.debug('app.run');

    $rootScope.pathTpl = settings.pathTpl;
    $rootScope.prefix = settings.classNamePrefix;

    $q.all({
      contract: ContractService.getRevisionOptions(),
      details: ContractDetailsService.getOptions(),
      hour: ContractHourService.getOptions(),
      pay: ContractPayService.getOptions(),
      leave: ContractLeaveService.getOptions(),
      health: ContractHealthService.getOptions(),
      pension: ContractPensionService.getOptions()
    })
    .then(function (results) {
      results.pay.pay_is_auto_est = ['No', 'Yes'];
      results.pension.is_enrolled = ['No', 'Yes', 'Opted out'];

      $log.debug('OPTIONS:');
      $log.debug(results);
      $rootScope.options = results;
    });
  }
});
