define([
  'common/angular',
  'common/decorators/ui-select/ui-select-choices',
  'common/decorators/angular-date/datepicker-addon',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services',
  'common/modules/apis'
], function (angular, uiSelectChoicesCustomScrollBars, datepickerAddon) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'common.apis', 'ui.select', 'ngSanitize'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uiSelectChoicesDirective', uiSelectChoicesCustomScrollBars);
      $provide.decorator('uibDatepickerPopupDirective', datepickerAddon);
    }]);
});
