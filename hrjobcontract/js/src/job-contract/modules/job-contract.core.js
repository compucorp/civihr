/* eslint-env amd */

define([
  'common/angular',
  'common/ui-select',
  'common/services/dom-event-trigger',
  'common/services/angular-date/date-format',
  'common/modules/routers/compu-ng-route',
  'common/modules/directives',
  'common/directives/angular-date/date-input',
  'common/filters/time-unit-applier.filter',
  'leave-absences/shared/models/absence-type.model',
  'job-contract/vendor/fraction',
  'job-contract/vendor/job-summary'
], function (angular) {
  'use strict';

  angular.module('job-contract.core', [
    'ngAnimate',
    'compuNgRoute',
    'ngResource',
    'angularFileUpload',
    'ui.bootstrap',
    'ui.select',
    'common.angularDate',
    'common.services',
    'common.directives',
    'common.filters',
    'leave-absences.models'
  ]);
});
