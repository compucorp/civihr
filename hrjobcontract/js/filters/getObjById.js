define(['filters/filters'], function(filters){
    filters.filter('getObjById',['$log',function($log){
        $log.debug('Filter: getObjById');

        return function(input, id, key) {
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