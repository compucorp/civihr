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
], function (angular, ContractCtrl, ContractListCtrl, RevisionListCtrl, FormGeneralCtrl,
  FormHealthCtrl, FormHourCtrl, FormLeaveCtrl, FormPayCtrl, FormPensionCtrl,
  ModalChangeReasonCtrl, ModalContractNewCtrl, ModalContractCtrl, ModalDialogCtrl,
  ModalProgressCtrl, ModalRevisionCtrl) {
  'use strict';

  return angular.module('job-contract.controllers', [])
    .controller(ContractCtrl.__name, ContractCtrl)
    .controller(ContractListCtrl.__name, ContractListCtrl)
    .controller(RevisionListCtrl.__name, RevisionListCtrl)
    .controller(FormGeneralCtrl.__name, FormGeneralCtrl)
    .controller(FormHealthCtrl.__name, FormHealthCtrl)
    .controller(FormHourCtrl.__name, FormHourCtrl)
    .controller(FormLeaveCtrl.__name, FormLeaveCtrl)
    .controller(FormPayCtrl.__name, FormPayCtrl)
    .controller(FormPensionCtrl.__name, FormPensionCtrl)
    .controller(ModalChangeReasonCtrl.__name, ModalChangeReasonCtrl)
    .controller(ModalContractNewCtrl.__name, ModalContractNewCtrl)
    .controller(ModalContractCtrl.__name, ModalContractCtrl)
    .controller(ModalDialogCtrl.__name, ModalDialogCtrl)
    .controller(ModalProgressCtrl.__name, ModalProgressCtrl)
    .controller(ModalRevisionCtrl.__name, ModalRevisionCtrl);
});
