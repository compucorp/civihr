define([
    'job-contract/filters/filters'
], function (filters) {
    'use strict';

    filters.filter('getObjById',['$log', function ($log) {
        $log.debug('Filter: getObjById');

        return function(input, id, key) {

            if (!input) {
                return null
            }

            var i=0, len=input.length;
            for (; i<len; i++) {
                if (+input[i].id == +id) {
                    return !key ? input[i] : input[i][key];
                }
            }
            return null;
        }
    }]);
});
