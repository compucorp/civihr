/* eslint-env amd */

define([
  'mocks/module',
  'mocks/data/work-pattern-data',
  'common/angularMocks'
], function (mocks, workPatternMocked) {
  'use strict';

  mocks.factory('WorkPatternAPIMock', ['$q', function ($q) {
    return {
      getCalendar: function (params) {
        return $q.resolve(workPatternMocked.getCalendar);
      }
    };
  }]);
});
