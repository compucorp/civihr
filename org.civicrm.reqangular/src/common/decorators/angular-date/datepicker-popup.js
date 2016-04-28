define([], function () {
    return ['$delegate', 'HR_settings', '$uibPosition', '$window', function ($delegate, HR_settings, $position, $window) {
        'use strict';

        var original_link = $delegate[0].link;

        $delegate[0].compile = function () {

            /**
             * Compile returns a link function.
             * @override
             */
            return function (scope, element, attrs, ngModel) {

                /**
                 * @override
                 * @type {string}
                 */
                attrs.uibDatepickerPopup = attrs.uibDatepickerPopup || HR_settings.DATE_FORMAT || 'yyyy-MM-dd';

                original_link.apply(this, arguments);
            };
        };

        return $delegate;
    }];
});

