/* eslint-env amd */

define([
  'common/angular',
  'job-roles/controllers/job-roles.controller',
  'job-roles/controllers/modal-dialog.controller'
], function (angular, JobRolesController, ModalDialogController) {
  'use strict';

  return angular.module('hrjobroles.controllers', [])
    .controller(JobRolesController.__name, JobRolesController)
    .controller(ModalDialogController.__name, ModalDialogController);
});
