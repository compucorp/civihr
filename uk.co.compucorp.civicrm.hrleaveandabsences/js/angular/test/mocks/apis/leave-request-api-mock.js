//intercepts paths for real APIs and returns mock data
define([
  'mocks/module',
  'mocks/data/leave-request-data',
  'common/angularMocks',
], function (mocks, mockData) {
  'use strict';

  mocks.factory('LeaveRequestAPIMock', [
    '$q',
    function ($q) {

      return {
        all: function (filters, pagination, sort, params) {
          return $q(function (resolve, reject) {
            resolve(mockData.all().values);
          });
        },
        balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
          return $q(function (resolve, reject) {
            resolve(mockData.balanceChangeByAbsenceType().values);
          });
        }
      };
    }]);
});
