module.exports = function ($filter, DateFactory) {
    var dateFormat = null;
    var filter = function (datetime, format) {
        var Date;
        dateFormat = format || dateFormat;

        if (typeof datetime == 'object') {
            datetime = $filter('date')(datetime, 'dd/MM/yyyy');
        }

        Date = DateFactory.createDate(datetime, [
            'DD-MM-YYYY',
            'DD-MM-YYYY HH:mm:ss',
            'YYYY-MM-DD',
            'YYYY-MM-DD HH:mm:ss',
            'DD/MM/YYYY',
            'x'
        ], true);

        var beginningOfEra = DateFactory.createDate(0);
        var notEmpty = !Date.isSame(beginningOfEra);

        if (!dateFormat) {
            DateFormatFactory.getDateFormat().then(function (result) {
                dateFormat = result;
            });
        }

        if (Date.isValid() && notEmpty) return Date.format(dateFormat);

        return 'Unspecified';
    };

    filter.$stateful = true;

    return filter;
};
