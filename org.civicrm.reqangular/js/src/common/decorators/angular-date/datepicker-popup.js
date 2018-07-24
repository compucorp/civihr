define([
    'common/lodash'
], function (_) {
    return ['$delegate', 'HR_settings', function ($delegate, HR_settings) {
        'use strict';

        var original_link = $delegate[0].link;

        $delegate[0].compile = function () {

            /**
             * Compile returns a link function.
             * @override
             */
            return function (scope, element, attrs, ngModel) {
                attrs.uibDatepickerPopup = attrs.uibDatepickerPopup || HR_settings.DATE_FORMAT || 'yyyy-MM-dd';
                attrs.showButtonBar = attrs.showButtonBar || false;

                scope.datepickerOptions = _.assign({
                    formatDayHeader: 'E',
                    showWeeks: false,
                    startingDay: 1
                }, scope.datepickerOptions);

                original_link.apply(this, arguments);
            };
        };

        return $delegate;
    }];
});
