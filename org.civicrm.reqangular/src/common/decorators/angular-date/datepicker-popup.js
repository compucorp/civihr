define([], function () {
    return ['$delegate', 'HR_settings', '$position', '$window', function ($delegate, HR_settings, $position, $window) {
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
                attrs.datepickerPopup = HR_settings.DATE_FORMAT;

                /**
                 * Calculates position of the datepicker popup.
                 * @param {bool} isDatepickerOpen
                 */
                var adjustPopupPosition = function (isDatepickerOpen) {
                    if (isDatepickerOpen) {
                        var popupPosition;
                        var popupWidth = element.parent().find('.datepicker-popup').outerWidth();

                        scope.popupPosition = $position.position(element);
                        scope.popupPosition.top += scope.popupPosition.height;

                        popupPosition = element.offset().left + element.width();
                        popupPosition += popupWidth;

                        if (popupPosition > $window.innerWidth) {
                            scope.popupPosition.left -= popupPosition - $window.innerWidth;
                        }
                    }
                };

                scope.$watch('isOpen', adjustPopupPosition);

                original_link.apply(this, arguments);
            };
        };

        return $delegate;
    }];
});

