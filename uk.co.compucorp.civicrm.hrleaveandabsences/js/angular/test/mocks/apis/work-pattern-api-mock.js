/* eslint-env amd */

define([
  'common/lodash',
  'mocks/module',
  'mocks/data/work-pattern-data'
], function (_, mocks, workPatternMocked) {
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
        return $q.resolve(workPatternMocked.workPatternsOf.values.map(storeWorkPattern));
      },
      unassignWorkPattern: function (contactWorkPatternID) {
        return $q.resolve({ values: [] });
      }
    };

    /**
     * ContactWorkPatterns data will have key 'api.WorkPattern.get'
     * which is normalized with a friendlier 'workPatterns' key
     *
     * @param  {Object} workPattern
     * @return {Object}
     */
    function storeWorkPattern (workPattern) {
      var clone = _.clone(workPattern);

      clone['workPattern'] = clone['api.WorkPattern.get']['values'][0];
      delete clone['api.WorkPattern.get'];

      return clone;
    }
  }]);
});
