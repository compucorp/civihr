define(['filters/filters'], function(filters){
    filters.filter('formatPeriod',['$filter','$log', function($filter, $log){
        $log.debug('Filter: formatPeriod');

        return function(period) {
            return period ? $filter('date')(period, 'yyyy/MM/dd') : 'Unspecified';
        }
    }]);
});