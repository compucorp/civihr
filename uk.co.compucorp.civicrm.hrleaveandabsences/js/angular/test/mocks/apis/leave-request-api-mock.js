/* eslint-env amd */

// intercepts paths for real APIs and returns mock data
define([
  'common/lodash',
  'mocks/module',
  'mocks/data/leave-request-data',
  'mocks/data/comments-data',
  'mocks/data/sickness-leave-request-data',
  'common/angularMocks'
], function (_, mocks, mockData, commentsMock, sicknessMockData) {
  'use strict';

  mocks.factory('LeaveRequestAPIMock', [
    '$q',
    function ($q) {
      return {
        all: function (filters, pagination, sort, params, cache) {
          return $q(function (resolve, reject) {
            var list = mockData.all().values;

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
              if (params.from_date_type === 'half_day_am') {
                resolve(mockData.singleDayCalculateBalanceChange().values);
              } else if (params.from_date_type === 'all_day') {
                resolve(mockData.multipleDayCalculateBalanceChange().values);
              }
            }

            resolve(mockData.calculateBalanceChange().values);
          });
        },
        create: function (params) {
          return $q(function (resolve, reject) {
            if (!params.contact_id || !params.from_date) {
              reject('contact_id, from_date and from_date_type in params are mandatory');
            }

            if (params.request_type === 'sick') {
              resolve(sicknessMockData.all().values[0]);
            } else {
              resolve(mockData.all().values[0]);
            }
          });
        },
        delete: function (id) {
          return $q.resolve(mockData.delete());
        },
        update: function (params) {
          return $q(function (resolve, reject) {
            var newAttributes;

            if (params.request_type === 'sick') {
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
        isValid: function (params) {
          return $q(function (resolve, reject) {
            if (!params.contact_id || !params.from_date) {
              var errorMessage = _.map(mockData.getNotIsValid().values, function (value) {
                return _.isArray(value) ? value.join(', ') : JSON.stringify(value);
              }).join(', ');

              reject(errorMessage);
            }

            resolve(mockData.getisValid().values);
          });
        },
        saveComment: function (params) {
          return $q(function (resolve, reject) {
            resolve(commentsMock.getCommentsWithMixedIDs().values[0]);
          });
        },
        getComments: function (params) {
          return $q(function (resolve, reject) {
            resolve(commentsMock.getComments().values);
          });
        },
        deleteComment: function (params) {
          return $q(function (resolve, reject) {
            resolve(commentsMock.deleteComment().values);
          });
        },
        getAttachments: function (params) {
          return $q(function (resolve, reject) {
            resolve(mockData.getAttachments().values);
          });
        },
        deleteAttachment: function (params) {
          return $q(function (resolve, reject) {
            resolve(mockData.deleteAttachment().values);
          });
        },
        find: function (id) {
          return $q(function (resolve, reject) {
            resolve(mockData.singleDataSuccess());
          });
        }
      };
    }
  ]);
});
