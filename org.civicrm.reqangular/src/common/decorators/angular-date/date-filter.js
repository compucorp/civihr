define([], function () {
    'use strict';

    /**
     * Extends the default dateFormat to support the 'E' format
     * The 'E' format returns only the first letter of a week day (M, T, W, etc)
     */
    return ['$delegate', function ($delegate) {
        var srcFilter = $delegate;

        return function () {
            if (arguments[1] === 'E') {
                var newArgs = Array.prototype.slice.call(arguments);
                newArgs[1] = 'EEE';

                return srcFilter.apply(this, newArgs)[0];
            } else {
                return srcFilter.apply(this, arguments);
            }
        };
    }];
});
