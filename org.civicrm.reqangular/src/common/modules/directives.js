define([
  'common/angular',
  'common/decorators/ui-select/ui-select-choices-custom-scrollbars',
  'common/decorators/ui-select/ui-select',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services'
], function (angular, uiSelectChoicesCustomScrollBars, uiSelect) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'common.apis', 'ui.select', 'ngSanitize']).config(['$provide', function ($provide) {
      $provide.decorator('uiSelectChoicesDirective', uiSelectChoicesCustomScrollBars);
      $provide.decorator('uiSelectDirective', uiSelect);
    }]);
});
