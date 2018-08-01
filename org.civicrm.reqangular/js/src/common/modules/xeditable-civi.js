define([
  'common/angular',
  'common/decorators/xeditable-civi/editable-directive-factory',
  'common/directives/xeditable-civi/editable-ta',
  'common/angularXeditable',
  'common/text-angular'
], function (angular, editableDirectiveFactory, editableTa) {
  'use strict';

  return angular.module('xeditable-civi', ['xeditable', 'textAngular'])
    .directive('editableTa', editableTa);
});
