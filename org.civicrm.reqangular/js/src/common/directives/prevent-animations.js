define([
    'common/modules/directives'
], function (directives) {
    'use strict';

    directives.directive('preventAnimations', ['$animate', function ($animate) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                $animate.enabled(element, false);
            }
        };
    }]);
});
