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
  'job-contract/controllers/modal/modal-revision.controller'
], function (angular, ContractController, ContractListController, RevisionListController, FormGeneralController,
  FormHealthController, FormHourController, FormLeaveController, FormPayController, FormPensionController,
  ModalChangeReasonController, ModalContractNewController, ModalContractController, ModalDialogController,
  ModalProgressController, ModalRevisionController) {
  'use strict';

  return angular.module('job-contract.controllers', [])
    .controller(ContractController.__name, ContractController)
    .controller(ContractListController.__name, ContractListController)
    .controller(RevisionListController.__name, RevisionListController)
    .controller(FormGeneralController.__name, FormGeneralController)
    .controller(FormHealthController.__name, FormHealthController)
    .controller(FormHourController.__name, FormHourController)
    .controller(FormLeaveController.__name, FormLeaveController)
    .controller(FormPayController.__name, FormPayController)
    .controller(FormPensionController.__name, FormPensionController)
    .controller(ModalChangeReasonController.__name, ModalChangeReasonController)
    .controller(ModalContractNewController.__name, ModalContractNewController)
    .controller(ModalContractController.__name, ModalContractController)
    .controller(ModalDialogController.__name, ModalDialogController)
    .controller(ModalProgressController.__name, ModalProgressController)
    .controller(ModalRevisionController.__name, ModalRevisionController);
});
