/* eslint-env amd */

define([
  'common/angular',
  'job-contract/services/api.service',
  'job-contract/services/contact.service',
  'job-contract/services/contract.service',
  'job-contract/services/contract-details.service',
  'job-contract/services/contract-files.service',
  'job-contract/services/contract-health.service',
  'job-contract/services/contract-hour.service',
  'job-contract/services/contract-leave.service',
  'job-contract/services/contract-pay.service',
  'job-contract/services/contract-pension.service',
  'job-contract/services/contract-revision-list.service',
  'job-contract/services/contract-revision.service',
  'job-contract/services/contract-service.service',
  'job-contract/services/utils.service'
], function (angular, apiService, contactService, contract, contractDetailsService,
  contractFilesService, contractHealthService, contractHourService, contractLeaveService,
  contractPayService, contractPensionService, contractRevisionListService, contractRevisionService,
  contractService, utilsService) {
  'use strict';

  return angular.module('job-contract.services', [])
    .factory(apiService)
    .factory(contactService)
    .factory(contract)
    .factory(contractDetailsService)
    .factory(contractFilesService)
    .factory(contractHealthService)
    .factory(contractHourService)
    .factory(contractLeaveService)
    .factory(contractPayService)
    .factory(contractPensionService)
    .factory(contractRevisionListService)
    .factory(contractRevisionService)
    .factory(contractService)
    .factory(utilsService);
});
