define([
  'mocks/module',
  'mocks/data/work-pattern-data',
  'common/angularMocks',
], function (mocks, mockData) {
  'use strict';

  mocks.factory('WorkPatternAPI', ['$q', function ($q) {
    return {
      getCalendar: function (params) {
        return $q(function (resolve, reject) {
          resolve(mockData.daysData());
        });
      }
    };
  }]);
});
