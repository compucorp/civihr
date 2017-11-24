define([
  'common/moment',
  'common/modules/angular-date',
  'common/services/hr-settings'
], function (moment, module) {
  'use strict';

  module.filter('formatDate', ['HR_settings', function (HR_settings) {
    var validFormats = ['DD-MM-YYYY', 'DD-MM-YYYY HH:mm:ss', 'YYYY-MM-DD',
      'YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY', 'x'];

    return function (datetime, format) {
      var date;
      var dateFormat = format || HR_settings.DATE_FORMAT || 'YYYY-MM-DD';
      var beginningOfEra = moment(0);

      if (datetime instanceof Date) {
        datetime = moment(datetime).format('YYYY-MM-DD HH:mm:ss');
      }

      date = moment(datetime, validFormats, true);

      if (date.isValid() && !date.isSame(beginningOfEra)) {
        return format === Date ? date.toDate() : date.format(dateFormat.toUpperCase());
      }

      return 'Unspecified';
    };
  }]);
});
