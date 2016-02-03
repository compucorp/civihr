define([
    'common/modules/angularDate',
    'common/services/settings/HR_settings'
], function (directives) {
    'use strict';

    directives.filter('formatDate', ['$filter', 'DateFactory', 'HR_settings', function ($filter, DateFactory, HR_settings) {
        return function (datetime, format) {
            var Date;
            var dateFormat = format || HR_settings.DATE_FORMAT;

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

            if (Date.isValid() && notEmpty) return Date.format(dateFormat);

            return 'Unspecified';
        };
    }]);
});
