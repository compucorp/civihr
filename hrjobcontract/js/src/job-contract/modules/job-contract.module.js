/* eslint-env amd */

define([
  'common/angular',
  'job-contract/controllers/contract.controller',
  'job-contract/controllers/contract-list.controller',
  'job-contract/controllers/revision-list.controller',
  'job-contract/controllers/form/form-general.controller',
  'job-contract/controllers/form/form-health.controller',
  'job-contract/controllers/form/form-hour.controller',
  'job-contract/controllers/form/form-leave.controller',
  'job-contract/controllers/form/form-pay.controller',
  'job-contract/controllers/form/form-pension.controller',
  'job-contract/controllers/modal/modal-change-reason.controller',
  'job-contract/controllers/modal/modal-contract-new.controller',
  'job-contract/controllers/modal/modal-contract.controller',
  'job-contract/controllers/modal/modal-dialog.controller',
  'job-contract/controllers/modal/modal-progress.controller',
  'job-contract/controllers/modal/modal-revision.controller',
  'job-contract/directives/contact.directive',
  'job-contract/directives/loader.directive',
  'job-contract/directives/number.directive',
  'job-contract/directives/validate.directive',
  'job-contract/filters/capitalize.filter',
  'job-contract/filters/format-amount.filter',
  'job-contract/filters/format-period.filter',
  'job-contract/filters/get-obj-by-id.filter',
  'job-contract/filters/parse-integer.filter',
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
  'job-contract/services/utils.service',
  'common/services/notification.service',
  'job-contract/modules/job-contract.config',
  'job-contract/modules/job-contract.constants',
  'job-contract/modules/job-contract.core',
  'job-contract/modules/job-contract.run'
], function (angular, ContractController, ContractListController, RevisionListController,
  FormGeneralController, FormHealthController, FormHourController, FormLeaveController,
  FormPayController, FormPensionController, ModalChangeReasonController, ModalContractNewController,
  ModalContractController, ModalDialogController, ModalProgressController, ModalRevisionController,
  hrjcContact, hrjcLoader, hrjcNumber, hrjcValidate, capitalize, formatAmount,
  formatPeriod, getObjById, parseInteger, apiService, contactService, contract,
  contractDetailsService, contractFilesService, contractHealthService, contractHourService,
  contractLeaveService, contractPayService, contractPensionService, contractRevisionListService,
  contractRevisionService, contractService, utilsService) {
  'use strict';

  angular.module('job-contract', [
    'common.services',
    'job-contract.core',
    'job-contract.config',
    'job-contract.run',
    'job-contract.constants'
  ])
    .controller(ContractController)
    .controller(ContractListController)
    .controller(RevisionListController)
    .controller(FormGeneralController)
    .controller(FormHealthController)
    .controller(FormHourController)
    .controller(FormLeaveController)
    .controller(FormPayController)
    .controller(FormPensionController)
    .controller(ModalChangeReasonController)
    .controller(ModalContractNewController)
    .controller(ModalContractController)
    .controller(ModalDialogController)
    .controller(ModalProgressController)
    .controller(ModalRevisionController)
    .directive(hrjcContact)
    .directive(hrjcLoader)
    .directive(hrjcNumber)
    .directive(hrjcValidate)
    .filter(capitalize)
    .filter(formatAmount)
    .filter(formatPeriod)
    .filter(getObjById)
    .filter(parseInteger)
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
