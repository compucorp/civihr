define([], function () {

    /**
     * Decorates Daypicker directive, so that it uses custom template.
     */
    return ['$delegate', function ($delegate) {

        /**
         * @override
         *
         * template path
         * @type {string}
         */
        $delegate[0].templateUrl = "angular-date/day.html";

        return $delegate;
    }];
});
