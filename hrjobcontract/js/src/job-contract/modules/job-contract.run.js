/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('job-contract.run', ['job-contract.constants']).run(run);

  run.$inject = [
    '$log', '$q', '$rootScope', 'settings', 'contractService', 'contractDetailsService',
    'contractHourService', 'contractPayService', 'contractLeaveService',
    'contractHealthService', 'contractPensionService'
  ];

  function run ($log, $q, $rootScope, settings, contractService, contractDetailsService,
    contractHourService, contractPayService, contractLeaveService, contractHealthService,
    contractPensionService) {
    $log.debug('app.run');

    $rootScope.pathTpl = settings.pathTpl;
    $rootScope.prefix = settings.classNamePrefix;

    $q.all({
      contract: contractService.getRevisionOptions(),
      details: contractDetailsService.getOptions(),
      hour: contractHourService.getOptions(),
      pay: contractPayService.getOptions(),
      leave: contractLeaveService.getOptions(),
      health: contractHealthService.getOptions(),
      pension: contractPensionService.getOptions()
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
