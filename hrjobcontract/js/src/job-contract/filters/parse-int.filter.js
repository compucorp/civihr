define([
    'job-contract/modules/job-contract.filters'
], function (filters) {
    'use strict';

    filters.filter('parseInt',['$log', function ($log) {
        $log.debug('Filter: parseInt');

        return function(input) {
            return input ? parseInt(input) : null;
        }
    }]);
});
