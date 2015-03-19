define(['filters/filters'], function(filters){
    filters.filter('formatAmount',['$log',function($log){
        $log.debug('Filter: formatAmount');

        return function(input) {
            return input && input.indexOf('.') === -1 ? input+'.00' : input
        }
    }]);
});