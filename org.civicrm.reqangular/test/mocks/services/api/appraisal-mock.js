/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angular',
  'common/mocks/module'
], function (_, angular, mocks) {
  'use strict';

  mocks.factory('api.appraisal.mock', ['$q', function ($q) {
    return {
      all: function (filters, pagination, value) {
        var list, start, end;

        list = value || this.mockedAppraisals().list;

        if (filters) {
          list = list.filter(function (appraisal) {
            return Object.keys(filters).every(function (key) {
              return appraisal[key] === filters[key];
            });
          });
        }

        if (pagination) {
          start = (pagination.page - 1) * pagination.size;
          end = start + pagination.size;

          list = list.slice(start, end);
        }

        return promiseResolvedWith({
          list: list,
          total: list.length,
          allIds: list.map(function (appraisal) {
            return appraisal.id;
          }).join(',')
        });
      },
      create: function (attributes, value) {
        var created = value || (function () {
          var created = angular.copy(attributes);

          created.id = '' + Math.ceil(Math.random() * 5000);
          created.createdAt = Date.now();

          return created;
        })();

        return promiseResolvedWith(created);
      },
      find: function (id, value) {
        var appraisal = value || this.mockedAppraisals().list.filter(function (appraisal) {
          return appraisal.id === id;
        })[0];

        return promiseResolvedWith(appraisal);
      },
      overdue: function (filters) {
                // Just take the first 5 appraisals, independent from the cycle id
        var list = this.mockedAppraisals().list.slice(0, 5);

        return promiseResolvedWith({
          list: list,
          total: list.length,
          allIds: list.map(function (appraisal) {
            return appraisal.id;
          }).join(',')
        });
      },

            /**
             * Adds a spy on every method for testing purposes
             */
      spyOnMethods: function () {
        _.functions(this).forEach(function (method) {
          spyOn(this, method).and.callThrough();
        }.bind(this));
      },

            /**
             * # DRAFT #
             *
             * Mocked appraisals
             */
      mockedAppraisals: function () {
        return {
          total: 10,
          list: [
            {
              id: '3451',
              appraisal_cycle_id: '1',
              self_appraisal_due: '2016-01-01',
              manager_appraisal_due: '2016-02-02',
              grade_due: '2016-03-03',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '2',
              original_id: '3451',
              created_date: '2015-01-01',
              is_current: '1',
              contact: {
                id: '201',
                display_name: 'Contact #1'
              },
              manager: {
                id: '301',
                display_name: 'Manager #1'
              },
              role: {
                title: 'Role #1',
                level: 'Level #1',
                location: 'Location #1'
              }
            },
            {
              id: '3452',
              appraisal_cycle_id: '1',
              self_appraisal_due: '2016-02-02',
              manager_appraisal_due: '2016-03-03',
              grade_due: '2016-04-04',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '3',
              original_id: '3452',
              created_date: '2015-02-02',
              is_current: '1',
              contact: {
                id: '202',
                display_name: 'Contact #2'
              },
              manager: {
                id: '302',
                display_name: 'Manager #2'
              },
              role: {
                title: 'Role #2',
                level: 'Level #2',
                location: 'Location #2'
              }
            },
            {
              id: '3453',
              appraisal_cycle_id: '1',
              self_appraisal_due: '2016-03-03',
              manager_appraisal_due: '2016-04-04',
              grade_due: '2016-05-05',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '2',
              original_id: '3453',
              created_date: '2015-03-03',
              is_current: '1',
              contact: {
                id: '203',
                display_name: 'Contact #3'
              },
              manager: {
                id: '303',
                display_name: 'Manager #3'
              },
              role: {
                title: 'Role #3',
                level: 'Level #3',
                location: 'Location #3'
              }
            },
            {
              id: '3454',
              appraisal_cycle_id: '2',
              self_appraisal_due: '2016-04-04',
              manager_appraisal_due: '2016-05-05',
              grade_due: '2016-06-06',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '3',
              original_id: '3454',
              created_date: '2015-04-04',
              is_current: '1',
              contact: {
                id: '204',
                display_name: 'Contact #4'
              },
              manager: {
                id: '304',
                display_name: 'Manager #4'
              },
              role: {
                title: 'Role #4',
                level: 'Level #1',
                location: 'Location #1'
              }
            },
            {
              id: '3455',
              appraisal_cycle_id: '2',
              self_appraisal_due: '2016-05-05',
              manager_appraisal_due: '2016-06-06',
              grade_due: '2016-07-07',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '1',
              original_id: '3455',
              created_date: '2015-05-05',
              is_current: '1',
              contact: {
                id: '205',
                display_name: 'Contact #5'
              },
              manager: {
                id: '305',
                display_name: 'Manager #5'
              },
              role: {
                title: 'Role #5',
                level: 'Level #2',
                location: 'Location #2'
              }
            },
            {
              id: '3456',
              appraisal_cycle_id: '2',
              self_appraisal_due: '2016-06-06',
              manager_appraisal_due: '2016-07-07',
              grade_due: '2016-08-08',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '1',
              original_id: '3456',
              created_date: '2015-06-06',
              is_current: '0',
              contact: {
                id: '206',
                display_name: 'Contact #6'
              },
              manager: {
                id: '306',
                display_name: 'Manager #6'
              },
              role: {
                title: 'Role #6',
                level: 'Level #3',
                location: 'Location #3'
              }
            },
            {
              id: '3457',
              appraisal_cycle_id: '2',
              self_appraisal_due: '2016-07-07',
              manager_appraisal_due: '2016-08-08',
              grade_due: '2016-09-09',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '1',
              original_id: '3457',
              created_date: '2015-07-07',
              is_current: '0',
              contact: {
                id: '207',
                display_name: 'Contact #7'
              },
              manager: {
                id: '307',
                display_name: 'Manager #7'
              },
              role: {
                title: 'Role #7',
                level: 'Level #1',
                location: 'Location #1'
              }
            },
            {
              id: '3458',
              appraisal_cycle_id: '1',
              self_appraisal_due: '2016-08-08',
              manager_appraisal_due: '2016-09-09',
              grade_due: '2016-10-10',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '1',
              original_id: '3458',
              created_date: '2015-08-08',
              is_current: '0',
              contact: {
                id: '208',
                display_name: 'Contact #8'
              },
              manager: {
                id: '308',
                display_name: 'Manager #8'
              },
              role: {
                title: 'Role #8',
                level: 'Level #2',
                location: 'Location #2'
              }
            },
            {
              id: '3459',
              appraisal_cycle_id: '1',
              self_appraisal_due: '2016-09-09',
              manager_appraisal_due: '2016-10-10',
              grade_due: '2016-11-11',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '3',
              original_id: '3459',
              created_date: '2015-09-09',
              is_current: '1',
              contact: {
                id: '209',
                display_name: 'Contact #9'
              },
              manager: {
                id: '309',
                display_name: 'Manager #9'
              },
              role: {
                title: 'Role #9',
                level: 'Level #3',
                location: 'Location #3'
              }
            },
            {
              id: '3460',
              appraisal_cycle_id: '3',
              self_appraisal_due: '2016-10-10',
              manager_appraisal_due: '2016-11-11',
              grade_due: '2016-12-12',
              due_changed: '0',
              meeting_completed: '0',
              approved_by_employee: '0',
              status_id: '1',
              original_id: '3460',
              created_date: '2015-10-10',
              is_current: '1',
              contact: {
                id: '213',
                display_name: 'Contact #10'
              },
              manager: {
                id: '313',
                display_name: 'Manager #10'
              },
              role: {
                title: 'Role #10',
                level: 'Level #1',
                location: 'Location #1'
              }
            }
          ]
        };
      }
    };

        /**
         * Returns a promise that will resolve with the given value
         *
         * @param {any} value
         * @return {Promise}
         */
    function promiseResolvedWith (value) {
      var deferred = $q.defer();
      deferred.resolve(value);

      return deferred.promise;
    }
  }]);
});
