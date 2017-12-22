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
    .factory(apiService.__name, apiService)
    .factory(contactService.__name, contactService)
    .factory(contract.__name, contract)
    .factory(contractDetailsService.__name, contractDetailsService)
    .factory(contractFilesService.__name, contractFilesService)
    .factory(contractHealthService.__name, contractHealthService)
    .factory(contractHourService.__name, contractHourService)
    .factory(contractLeaveService.__name, contractLeaveService)
    .factory(contractPayService.__name, contractPayService)
    .factory(contractPensionService.__name, contractPensionService)
    .factory(contractRevisionListService.__name, contractRevisionListService)
    .factory(contractRevisionService.__name, contractRevisionService)
    .factory(contractService.__name, contractService)
    .factory(utilsService.__name, utilsService);
});
