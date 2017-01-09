define([
  'mocks/module',
  'mocks/data/absence-period-data',
  'common/moment',
  'common/angularMocks',
], function (mocks, mockData, moment) {
  'use strict';

  mocks.factory('AbsencePeriodAPIMock', ['$q', function ($q) {
    return {
      all: function (params) {
        return $q(function (resolve, reject) {
          if (params && 'start_date' in params) {
            var dateFormat = 'YYYY-MM-DD';
            //find if dates are in range else return null
            var checkDate = moment(params.start_date['<='], dateFormat);

            var mockPeriod = mockData.all().values.filter(function (value) {
              var startDate = moment(value.start_date);
              var endDate = moment(value.end_date);

              return checkDate.isBetween(startDate, endDate);
            });

            resolve(mockPeriod);
          }
          resolve(mockData.all().values);
        });
      }
    }
  }]);
});
