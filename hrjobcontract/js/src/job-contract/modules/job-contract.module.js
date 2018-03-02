/* eslint-env amd */

define([
  'common/angular',
  'common/services/notification.service',
  'job-contract/modules/job-contract.config',
  'job-contract/modules/job-contract.constants',
  'job-contract/modules/job-contract.controllers',
  'job-contract/modules/job-contract.core',
  'job-contract/modules/job-contract.directives',
  'job-contract/modules/job-contract.filters',
  'job-contract/modules/job-contract.run',
  'job-contract/modules/job-contract.services'
], function (angular) {
  'use strict';

  angular.module('job-contract', [
    'common.services',
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
