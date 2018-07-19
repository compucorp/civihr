define([
    'common/modules/directives',
    'common/directives/prevent-animations',
], function (directives) {
    'use strict';

    directives.directive('crmLoading', ['$templateCache', function ($templateCache) {
        return {
            scope: {
                show: '='
            },
            restrict: 'E',
            replace: true,
            transclude: true,
            template: $templateCache.get('loading.html')
        }
    }]);
});
