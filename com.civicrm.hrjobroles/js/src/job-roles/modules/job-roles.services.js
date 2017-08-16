/* eslint-env amd */

define([
  'common/angular',
  'job-roles/services/date-validation.service',
  'job-roles/services/filters.service',
  'job-roles/services/job-role.service'
], function (angular, dateValidation, filtersService, jobRoleService) {
  'use strict';

  return angular.module('hrjobroles.services', [])
    .factory('dateValidation', dateValidation)
    .factory('filtersService', filtersService)
    .factory('jobRoleService', jobRoleService);
});
