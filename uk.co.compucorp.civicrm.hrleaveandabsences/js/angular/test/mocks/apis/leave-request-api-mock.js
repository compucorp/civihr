//intercepts paths for real APIs and returns mock data
define([
  'mocks/module',
  'mocks/data/leave-request-data',
  'mocks/data/sickness-leave-request-data',
  'common/angularMocks',
], function (mocks, mockData, sicknessMockData) {
  'use strict';

  mocks.factory('LeaveRequestAPIMock', [
    '$q',
    function ($q) {

      return {
        all: function (filters, pagination, sort, params, cache, leaveRequestType) {
          return $q(function (resolve, reject) {
            var list = mockData.all().values;

            if (leaveRequestType && leaveRequestType === 'sick') {
              list = sicknessMockData.all().values;
            }

            resolve({
              list: list,
              total: list.length,
              allIds: list.map(function (leaveRequest) {
                return leaveRequest.id;
              }).join(',')
            });
          });
        },
        balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
          return $q(function (resolve, reject) {
            resolve(mockData.balanceChangeByAbsenceType().values);
          });
        },
        calculateBalanceChange: function (params) {
          return $q(function (resolve, reject) {
            if (params) {
              if (params.from_type === 'half_day_am') {
                resolve(mockData.singleDayCalculateBalanceChange().values);
              } else if (params.from_type === 'all_day') {
                resolve(mockData.multipleDayCalculateBalanceChange().values);
              }
            }

            resolve(mockData.calculateBalanceChange().values);
          });
        },
        create: function (params, leaveRequestType) {
          return $q(function (resolve, reject) {
            if (!params.contact_id || !params.from_date) {
              reject('contact_id, from_date and from_date_type in params are mandatory');
            }


            if (leaveRequestType && leaveRequestType === 'sick') {
              resolve(sicknessMockData.all().values[0]);
            } else {
              resolve(mockData.all().values[0]);
            }
          });
        },
        update: function (params, leaveRequestType) {
          return $q(function (resolve, reject) {
            var newAttributes;

            if (leaveRequestType && leaveRequestType === 'sick') {
              newAttributes = _.assign(Object.create(null), sicknessMockData.all().values[0], params);
            } else {
              newAttributes = _.assign(Object.create(null), mockData.all().values[0], params);
            }

            if (!params.id) {
              reject('id is mandatory field');
            }
            resolve(newAttributes);
          });
        },
        isValid: function (params, leaveRequestType) {
          return $q(function (resolve, reject) {
            if (!params.contact_id || !params.from_date) {
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
