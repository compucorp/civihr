define(['filters/filters'], function(filters){
    filters.filter('customDate', function($filter) {
        return function (datetime) {
            try{
                var match = datetime.match(/^(\d+)-(\d+)-(\d+)/), date;

                date = new Date(match[1], match[2] - 1, match[3]);

                return $filter('date')(date.getTime(), 'dd/MM/yyyy');
            }catch(e){
                return $filter('date')(datetime, 'dd/MM/yyyy');
            }

        };
    });
});