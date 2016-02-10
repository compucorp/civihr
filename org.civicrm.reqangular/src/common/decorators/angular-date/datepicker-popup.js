define([], function () {
    return ['$delegate', function ($delegate) {
        var original_link = $delegate[0].link;

        $delegate[0].compile = function () {

            /**
             * Compile returns a link function.
             * @override
             */
            return ['scope', 'element', 'attrs', 'ngModel', 'HR_settings',
                function (scope, element, attrs, ngModel, HR_settings) {

                /**
                 * @override
                 * @type {string}
                 */
                attrs.datepickerPopup = HR_settings.DATE_FORMAT;

                original_link.apply(this, arguments);
            }];
        };

        return $delegate;
    }];
});
