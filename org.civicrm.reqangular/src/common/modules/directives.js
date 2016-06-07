define([
  'common/angular',
  'common/decorators/ui-select/ui-select-choices-custom-scrollbars',
  'common/ui-select',
  'common/modules/templates'
], function (angular, uiSelectChoicesCustomScrollBars) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'ui.select', 'ngSanitize'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uiSelectChoicesDirective', uiSelectChoicesCustomScrollBars);
    }]);
});
