define([
    'common/moment',
    'common/modules/angular-date',
    'common/services/hr-settings'
], function (moment, module) {
    'use strict';

    module.filter('formatDate', ['$filter', 'HR_settings', function ($filter, HR_settings) {
        return function (datetime, format) {
            var date;
            var dateFormat = format || HR_settings.DATE_FORMAT || 'YYYY-MM-DD';

            if (typeof datetime == 'object') {
                datetime = $filter('date')(datetime, 'dd/MM/yyyy');
            }

            date = moment(datetime, [
                'DD-MM-YYYY',
                'DD-MM-YYYY HH:mm:ss',
                'YYYY-MM-DD',
                'YYYY-MM-DD HH:mm:ss',
                'DD/MM/YYYY',
                'x'
            ], true);

            var beginningOfEra = moment(0);
            var notEmpty = !date.isSame(beginningOfEra);

            if (date.isValid() && notEmpty) return date.format(dateFormat);

            return 'Unspecified';
        };
    }]);
});
