module.exports = function ($filter) {
    return function (datetime) {
        if(typeof datetime === 'string') {
            var match, match2, date;

            match = datetime.match(/^(\d{2})-(\d{2})-(\d{4})/);
            match2 = datetime.match(/^(\d{4})-(\d{2})-(\d{2})/);

            if(match){
                date = new Date(match[3], match[2] - 1, match[1]).getTime();
            } else if(match2){
                date = new Date(match2[1], match2[2] - 1, match2[3]).getTime();
            } else {
                date = datetime;
            }

            if(date < 0 || datetime.length < 10){
                return 'Unspecified';
            } else {
                return $filter('date')(date, 'dd/MM/yyyy');
            }

        } else if(typeof datetime === 'object' && datetime !== null ){
            if(datetime.getTime){
                return $filter('date')(datetime.getTime(), 'dd/MM/yyyy');
            }
        } else if(typeof datetime === 'number'){
            return $filter('date')(datetime, 'dd/MM/yyyy');
        }

        return null;
    };
};
