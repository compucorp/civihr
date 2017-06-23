/* eslint-env amd */

define([
  'mocks/module',
  'mocks/data/work-pattern-data',
  'common/angularMocks'
], function (mocks, workPatternMocked) {
  'use strict';

  mocks.factory('WorkPatternAPIMock', ['$q', function ($q) {
    return {
      assignWorkPattern: function (contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, params) {
        return $q.resolve({ values: [] });
      },
      get: function (params) {
        return $q.resolve(workPatternMocked.getAllWorkPattern.values);
      },
      getCalendar: function (params) {
        return $q.resolve(workPatternMocked.getCalendar);
      },
      workPatternsOf: function (contactId, params) {
        return $q.resolve(workPatternMocked.workPatternsOf.values);
      }
    };
  }]);
});
