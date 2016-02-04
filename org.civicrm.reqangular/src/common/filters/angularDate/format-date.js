define([
    'common/moment',
    'common/modules/angularDate',
    'common/services/settings/hr-settings'
], function (moment, module) {
    'use strict';

    module.filter('formatDate', ['$filter', 'HR_settings', function ($filter, HR_settings) {
        return function (datetime, format) {
            var Date;
            var dateFormat = format || HR_settings.DATE_FORMAT || 'YYYY-MM-DD';

            if (typeof datetime == 'object') {
                datetime = $filter('date')(datetime, 'dd/MM/yyyy');
            }

            Date = moment(datetime, [
                'DD-MM-YYYY',
                'DD-MM-YYYY HH:mm:ss',
                'YYYY-MM-DD',
                'YYYY-MM-DD HH:mm:ss',
                'DD/MM/YYYY',
                'x'
            ], true);

            var beginningOfEra = moment(0);
            var notEmpty = !Date.isSame(beginningOfEra);

            if (Date.isValid() && notEmpty) return Date.format(dateFormat);

            return 'Unspecified';
        };
    }]);
});
