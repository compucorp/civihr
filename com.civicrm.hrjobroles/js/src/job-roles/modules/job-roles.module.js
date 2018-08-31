/* eslint-env amd */

define([
  'common/angular',
  'job-roles/controllers/job-roles.controller',
  'job-roles/controllers/modal-dialog.controller',
  'job-roles/filters/get-active-values.filter',
  'job-roles/services/date-validation.service',
  'job-roles/services/filters.service',
  'job-roles/services/job-role.service',
  'job-roles/modules/job-roles.config',
  'job-roles/modules/job-roles.constants',
  'job-roles/modules/job-roles.core',
  'job-roles/modules/job-roles.run'
], function (angular, JobRolesController, ModalDialogController, getActiveValues,
  dateValidation, filtersService, jobRoleService) {
  'use strict';

  angular.module('hrjobroles', [
    'hrjobroles.core',
    'hrjobroles.config',
    'hrjobroles.run',
    'hrjobroles.constants'
  ])
    .controller(JobRolesController)
    .controller(ModalDialogController)
    .filter(getActiveValues)
    .factory(dateValidation)
    .factory(filtersService)
    .factory(jobRoleService);
});
