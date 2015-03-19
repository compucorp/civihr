define(['filters/filters'], function(filters){
    filters.filter('parseInt',['$log',function($log){
        $log.debug('Filter: parseInt');

        return function(input) {
            return input ? parseInt(input) : null;
        }
    }]);
});