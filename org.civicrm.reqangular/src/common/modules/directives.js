define([
  'common/angular',
  'common/decorators/ui-select/ui-select-choices',
  'common/decorators/ui-select/ui-select',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services'
], function (angular, uiSelectChoices, uiSelect) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'common.apis', 'ui.select', 'ngSanitize']).config(['$provide', function ($provide) {
      $provide.decorator('uiSelectDirective', uiSelect);
      $provide.decorator('uiSelectChoicesDirective', uiSelectChoices);
    }]);
});
