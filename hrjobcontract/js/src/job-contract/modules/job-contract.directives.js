/* eslint-env amd */

define([
  'common/angular',
  'job-contract/directives/contact.directive',
  'job-contract/directives/loader.directive',
  'job-contract/directives/number.directive',
  'job-contract/directives/validate.directive'
], function (angular, hrjcContact, hrjcLoader, hrjcNumber, hrjcValidate) {
  'use strict';

  return angular.module('job-contract.directives', [])
    .directive(hrjcContact.__name, hrjcContact)
    .directive(hrjcLoader.__name, hrjcLoader)
    .directive(hrjcNumber.__name, hrjcNumber)
    .directive(hrjcValidate.__name, hrjcValidate);
});
