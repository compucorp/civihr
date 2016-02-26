define([], function () {
    return ['$delegate', 'HR_settings', '$position', '$window', function ($delegate, HR_settings, $position, $window) {
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

                scope.$watch('isOpen', function (value) {
                    if (value) {
                        scope.popupPosition = $position.position(element);
                        scope.popupPosition.top += scope.popupPosition.height;

                        var popupWidth = 316;

                        var popupPosition = element.offset().left + element.width();
                        popupPosition += popupWidth;
                        
                        if( popupPosition > $window.innerWidth ){
                            var diff = popupPosition - $window.innerWidth;
                            scope.popupPosition.left -= diff;
                        }
                    }
                });

                original_link.apply(this, arguments);
            };
        };

        return $delegate;
    }];
});
