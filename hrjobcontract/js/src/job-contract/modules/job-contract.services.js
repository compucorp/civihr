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
], function (angular, API, ContactService, Contract, ContractDetailsService,
  ContractFilesService, ContractHealthService, ContractHourService, ContractLeaveService,
  ContractPayService, ContractPensionService, ContractRevisionList, ContractRevisionService,
  ContractService, UtilsService) {
  'use strict';

  return angular.module('job-contract.services', [])
    .factory(API.__name, API)
    .factory(ContactService.__name, ContactService)
    .factory(Contract.__name, Contract)
    .factory(ContractDetailsService.__name, ContractDetailsService)
    .factory(ContractFilesService.__name, ContractFilesService)
    .factory(ContractHealthService.__name, ContractHealthService)
    .factory(ContractHourService.__name, ContractHourService)
    .factory(ContractLeaveService.__name, ContractLeaveService)
    .factory(ContractPayService.__name, ContractPayService)
    .factory(ContractPensionService.__name, ContractPensionService)
    .factory(ContractRevisionList.__name, ContractRevisionList)
    .factory(ContractRevisionService.__name, ContractRevisionService)
    .factory(ContractService.__name, ContractService)
    .factory(UtilsService.__name, UtilsService);
});
