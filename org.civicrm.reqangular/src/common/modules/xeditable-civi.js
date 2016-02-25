define([
    'common/angular',
    'common/decorators/xeditable-civi/editable-directive-factory',
    'common/directives/xeditable-civi/editable-ta',
    'common/directives/xeditable-civi/editable-ui-select',
    'common/angularXeditable',
    'common/text-angular'
], function (angular, editableDirectiveFactory, editableTa, editableUiSelect) {
    'use strict';

    return angular.module('xeditable-civi', ['xeditable', 'textAngular'])
            .config(['$provide', function ($provide) {
                $provide.decorator('editableDirectiveFactory', editableDirectiveFactory);
            }])
            .directive('editableUiSelect', editableUiSelect)
            .directive('editableTa', editableTa);
});
