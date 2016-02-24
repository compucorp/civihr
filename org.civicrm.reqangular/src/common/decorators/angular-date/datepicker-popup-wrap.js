define([], function () {

    /**
     * Decorates DatepickerPopupWrap directive, so that it uses custom template.
     */

    return ['$delegate', function ($delegate) {

        /**
         * @override
         *
         * template path
         * @type {string}
         */
        $delegate[0].templateUrl = 'angular-date/datepickerPopup.html';

        return $delegate;
    }];
});
