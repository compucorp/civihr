define([], function () {
    return ['$delegate', 'HR_settings', function ($delegate, HR_settings) {
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
                attrs.datepickerPopup = HR_settings.DATE_FORMAT;

                original_link.apply(this, arguments);
            };
        };

        return $delegate;
    }];
});
