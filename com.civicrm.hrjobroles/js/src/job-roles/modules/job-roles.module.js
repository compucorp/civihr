/* eslint-env amd */

define([
  'common/angular',
  'job-roles/modules/job-roles.config',
  'job-roles/modules/job-roles.constants',
  'job-roles/modules/job-roles.controllers',
  'job-roles/modules/job-roles.core',
  'job-roles/modules/job-roles.filters',
  'job-roles/modules/job-roles.run',
  'job-roles/modules/job-roles.services'
], function (angular) {
  'use strict';

  angular.module('hrjobroles', [
    'hrjobroles.core',
    'hrjobroles.config',
    'hrjobroles.run',
    'hrjobroles.constants',
    'hrjobroles.controllers',
    'hrjobroles.filters',
    'hrjobroles.services'
  ]);
});
