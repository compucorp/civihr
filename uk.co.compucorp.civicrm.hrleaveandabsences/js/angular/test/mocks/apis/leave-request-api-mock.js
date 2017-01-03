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
        },
        calculateBalanceChange: function (params) {
          return $q(function (resolve, reject) {
            resolve(mockData.calculateBalanceChange().values);
          });
        },
        create: function (params) {
          return $q(function (resolve, reject) {
            if (!params.contact_id) {
              reject({
                is_error: 1,
                error_message: 'contact_id, from_date and from_date_type in params are mandatory'
              });
            }

            resolve(mockData.all().values[0]);
          });
        },
        update: function (params) {
          return $q(function (resolve, reject) {
            var newAttributes = _.assign(Object.create(null), mockData.all().values[0], params);
            if (!params.id) {
              reject({
                is_error: 1,
                error_message: 'id is mandatory field'
              });
            }
            resolve(newAttributes);
          });
        },
        isValid: function (params) {
          return $q(function (resolve, reject) {
            if (!params.contact_id) {
              reject(mockData.getNotIsValid().values);
            }

            resolve(mockData.getisValid().values);
          });
        },
        isManagedBy: function (params) {
          return $q(function (resolve, reject) {
            resolve(mockData.isManagedBy().values);
          });
        }
      };
    }
  ]);
});
