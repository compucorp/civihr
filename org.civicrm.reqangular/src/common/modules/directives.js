/* eslint-env amd */

define([
  'common/angular',
  'common/decorators/angular-date/datepicker-addon',
  'common/decorators/angular-date/xeditable-addon',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/angularBootstrap',
  'common/angularXeditable',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services',
  'common/modules/apis'
], function (angular, datepickerAddon, xeditableAddon, uibModalDecorator) {
  'use strict';
  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'common.apis', 'ui.select', 'ngSanitize', 'xeditable'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uibDatepickerPopupDirective', datepickerAddon);
      $provide.decorator('$uibModal', uibModalDecorator);
      $provide.decorator('editableBsdateDirective', xeditableAddon);
    }])
});
