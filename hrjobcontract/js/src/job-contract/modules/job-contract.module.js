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
  'job-contract/services/contract.service',
  'job-contract/services/contract-revision-list.service'
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
