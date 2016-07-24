define([
  'common/angular',
  'common/decorators/ui-select/ui-select-choices-custom-scrollbars',
  'common/decorators/ui-select/ui-select',
  'common/ui-select',
  'common/modules/templates'
], function (angular, uiSelectChoicesCustomScrollBars, uiSelect) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'ui.select', 'ngSanitize'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uiSelectChoicesDirective', uiSelectChoicesCustomScrollBars);
      $provide.decorator('uiSelectDirective', uiSelect);
    }]);
});
