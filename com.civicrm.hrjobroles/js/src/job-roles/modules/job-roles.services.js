/* eslint-env amd */

define([
  'common/angular',
  'job-roles/services/date-validation.service',
  'job-roles/services/filters.service',
  'job-roles/services/job-role.service'
], function (angular, dateValidation, filtersService, jobRoleService) {
  'use strict';

  return angular.module('hrjobroles.services', [])
    .factory(dateValidation.__name, dateValidation)
    .factory(filtersService.__name, filtersService)
    .factory(jobRoleService.__name, jobRoleService);
});
