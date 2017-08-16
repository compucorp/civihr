/* eslint-env amd */

define([
  'common/angular',
  'job-roles/modules/job-roles.config',
  'job-roles/modules/job-roles.constants',
  'job-roles/modules/job-roles.core',
  'job-roles/modules/job-roles.run',
  'job-roles/controllers/job-roles.controller',
  'job-roles/controllers/modal-dialog.controller',
  'job-roles/services/date-validation.service',
  'job-roles/services/filters.service',
  'job-roles/services/job-role.service'
], function (angular) {
  'use strict';

  angular.module('hrjobroles', [
    'hrjobroles.core',
    'hrjobroles.config',
    'hrjobroles.constants',
    'hrjobroles.run',
    'hrjobroles.controllers',
    'hrjobroles.filters',
    'hrjobroles.services'
  ]);
});
