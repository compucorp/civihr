/* eslint-env amd */

define([
  'common/angular',
  'common/ui-select',
  'common/directives/angular-date/date-input',
  'common/filters/angular-date/format-date',
  'common/filters/time-unit-applier.filter',
  'common/modules/routers/compu-ng-route',
  'common/modules/directives',
  'common/services/dom-event-trigger',
  'common/services/pub-sub',
  'common/services/angular-date/date-format',
  'common/services/crm-ang.service',
  'common/services/notification.service',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/models/absence-period.model',
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
