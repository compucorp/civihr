define([
  'mocks/module',
  'mocks/data/public-holiday-data',
  'common/moment',
  'common/angularMocks',
], function (mocks, mockData, moment) {
  'use strict';

  mocks.factory('PublicHolidayAPIMock', ['$q', function ($q) {
    return {
      all: function (params) {
        return $q(function (resolve, reject) {
          if (params && 'date' in params) {
            var dateFormat = 'YYYY-MM-DD';
            var checkDate = moment(params.date, dateFormat);

            var mockPeriod = mockData.all().values.filter(function (value) {
              var valueDate = moment(value.date);

              return checkDate.isSame(valueDate);
            });

            resolve(mockPeriod);
          }
          resolve(mockData.all().values);
        });
      }
    };
  }]);
});
