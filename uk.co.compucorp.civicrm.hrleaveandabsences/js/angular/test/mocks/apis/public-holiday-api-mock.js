define([
  'mocks/module',
  'mocks/data/public-holiday-data',
  'common/moment',
  'common/angularMocks',
], function (mocks, mockData, moment) {
  'use strict';

  mocks.factory('PublicHolidayAPIMock', ['$q', 'shared-settings', function ($q, sharedSettings) {
    return {
      all: function (params) {
        return $q(function (resolve, reject) {
          if (params && 'date' in params) {
            var checkDate = moment(params.date, sharedSettings.serverDateFormat);

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
