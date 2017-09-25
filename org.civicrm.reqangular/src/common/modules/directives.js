/* eslint-env amd */

define([
  'common/angular',
  'common/decorators/angular-date/datepicker-addon',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services',
  'common/modules/apis'
], function (angular, datepickerAddon, uibModalDecorator) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'common.apis', 'ui.select', 'ngSanitize'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uibDatepickerPopupDirective', datepickerAddon);
      $provide.decorator('$uibModal', uibModalDecorator);
    }]);
});
