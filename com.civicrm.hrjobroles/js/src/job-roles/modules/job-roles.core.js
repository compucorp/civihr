/* eslint-env amd */

define([
  'common/angular',
  'common/ui-select',
  'common/directives/angular-date/date-input',
  'common/modules/directives',
  'common/modules/routers/compu-ng-route',
  'common/filters/angular-date/format-date',
  'common/services/angular-date/date-format',
  'common/services/dom-event-trigger',
  'common/services/pub-sub',
  'job-roles/vendor/angular-editable',
  'job-roles/vendor/angular-filter'
], function (angular) {
  'use strict';

  angular.module('hrjobroles.core', [
    'angular.filter',
    'ngAnimate',
    'ngSanitize',
    'ngResource',
    'ui.bootstrap',
    'ui.select',
    'xeditable',
    'common.angularDate',
    'common.directives',
    'common.services',
    'compuNgRoute'
  ]);
});
