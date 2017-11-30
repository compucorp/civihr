/* eslint-env amd */

define([
  'common/angular',
  'job-contract/modules/job-contract.config',
  'job-contract/modules/job-contract.constants',
  'job-contract/modules/job-contract.controllers',
  'job-contract/modules/job-contract.core',
  'job-contract/modules/job-contract.directives',
  'job-contract/modules/job-contract.filters',
  'job-contract/modules/job-contract.run',
  'job-contract/modules/job-contract.services',
  'job-contract/controllers/contract-list.controller',
  'job-contract/controllers/contract.controller',
  'job-contract/controllers/revision-list.controller',
  'job-contract/controllers/modal/modal-change-reason.controller',
  'job-contract/controllers/modal/modal-contract.controller',
  'job-contract/controllers/modal/modal-contract-new.controller',
  'job-contract/controllers/modal/modal-dialog.controller',
  'job-contract/controllers/modal/modal-progress.controller',
  'job-contract/controllers/modal/modal-revision.controller',
  'job-contract/controllers/form/form-general.controller',
  'job-contract/controllers/form/form-hour.controller',
  'job-contract/controllers/form/form-health.controller',
  'job-contract/controllers/form/form-pay.controller',
  'job-contract/controllers/form/form-pension.controller',
  'job-contract/controllers/form/form-leave.controller',
  'job-contract/directives/contact.directive',
  'job-contract/directives/loader.directive',
  'job-contract/directives/number.directive',
  'job-contract/directives/validate.directive',
  'job-contract/filters/capitalize.filter',
  'job-contract/filters/get-obj-by-id.filter',
  'job-contract/filters/format-amount.filter',
  'job-contract/filters/format-period.filter',
  'job-contract/filters/parse-int.filter',
  'job-contract/services/contract.service',
  'job-contract/services/contract-revision-list.service',
], function (angular) {
  'use strict';

  angular.module('job-contract', [
    'job-contract.core',
    'job-contract.config',
    'job-contract.run',
    'job-contract.constants',
    'job-contract.controllers',
    'job-contract.directives',
    'job-contract.filters',
    'job-contract.services'
  ]);
});
