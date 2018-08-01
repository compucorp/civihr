/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angular',
  'common/mocks/module'
], function (_, angular, mocks) {
  'use strict';

  mocks.factory('api.appraisal-cycle.mock', ['$q', function ($q) {
    return {
      all: function (filters, pagination, value) {
        var list, start, end;

        list = value || this.mockedCycles().list;

        if (filters) {
          list = list.filter(function (cycle) {
            return Object.keys(filters).every(function (key) {
              return cycle[key] === filters[key];
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
          allIds: list.map(function (cycle) {
            return cycle.id;
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
        var cycle = value || this.mockedCycles().list.filter(function (cycle) {
          return cycle.id === id;
        })[0];

        return promiseResolvedWith(cycle);
      },
      grades: function (value) {
        var defaults = [
                    { label: '1', value: 30 },
                    { label: '2', value: 10 },
                    { label: '3', value: 55 },
                    { label: '4', value: 87 },
                    { label: '5', value: 54 }
        ];

        return promiseResolvedWith(value || defaults);
      },
      statuses: function (value) {
        var defaults = [
                    { id: '1', label: 'status 1', value: '1', weight: '1' },
                    { id: '2', label: 'status 2', value: '2', weight: '2' }
        ];

        return promiseResolvedWith(value || defaults);
      },
      statusOverview: function (params) {
        return promiseResolvedWith([
          {
            status_id: 1,
            status_name: 'Awaiting self appraisal',
            contacts_count: { due: 4, overdue: 2 }
          },
          {
            status_id: 2,
            status_name: 'Awaiting manager appraisal',
            contacts_count: { due: 10, overdue: 6 }
          },
          {
            status_id: 3,
            status_name: 'Awaiting grade',
            contacts_count: { due: 20, overdue: 12 }
          },
          {
            status_id: 4,
            status_name: 'Awaiting HR approval',
            contacts_count: { due: 7, overdue: 3 }
          },
          {
            status_id: 5,
            status_name: 'Complete',
            contacts_count: { due: 13, overdue: 8 }
          }
        ]);
      },
      update: function (id, attributes, value) {
        var cycle = value || (function () {
          var cycle = this.mockedCycles().list.filter(function (cycle) {
            return cycle.id === id;
          })[0];

          return angular.extend({}, cycle, attributes);
        }.bind(this))();

        return promiseResolvedWith(cycle);
      },
      total: function (filters, value) {
        var list = this.mockedCycles().list;

        if (filters) {
          list = list.filter(function (cycle) {
            return Object.keys(filters).every(function (key) {
              return cycle[key] === filters[key];
            });
          });
        }

        return promiseResolvedWith(list.length);
      },
      types: function (value) {
        var defaults = [
                    { id: '1', label: 'type 1', value: '1', weight: '1' },
                    { id: '2', label: 'type 2', value: '2', weight: '2' },
                    { id: '3', label: 'type 3', value: '3', weight: '3' }
        ];

        return promiseResolvedWith(value || defaults);
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
             * Mocked cycles
             */
      mockedCycles: function () {
        return {
          total: 10,
          list: [
            {
              id: '42131',
              cycle_name: 'Appraisal Cycle 1',
              cycle_is_active: true,
              cycle_type_id: '2',
              cycle_start_date: '2014-01-01',
              cycle_end_date: '2015-01-01',
              cycle_self_appraisal_due: '2016-01-01',
              cycle_manager_appraisal_due: '2016-01-02',
              cycle_grade_due: '2016-01-03'
            },
            {
              id: '42132',
              cycle_name: 'Appraisal Cycle 2',
              cycle_is_active: true,
              cycle_type_id: '1',
              cycle_start_date: '2014-02-02',
              cycle_end_date: '2015-02-02',
              cycle_self_appraisal_due: '2016-02-02',
              cycle_manager_appraisal_due: '2016-02-04',
              cycle_grade_due: '2016-02-05'
            },
            {
              id: '42133',
              cycle_name: 'Appraisal Cycle 3',
              cycle_is_active: true,
              cycle_type_id: '2',
              cycle_start_date: '2014-03-03',
              cycle_end_date: '2015-03-03',
              cycle_self_appraisal_due: '2016-03-06',
              cycle_manager_appraisal_due: '2016-03-07',
              cycle_grade_due: '2016-03-08'
            },
            {
              id: '42134',
              cycle_name: 'Appraisal Cycle 4',
              cycle_is_active: true,
              cycle_type_id: '3',
              cycle_start_date: '2014-04-04',
              cycle_end_date: '2015-04-04',
              cycle_self_appraisal_due: '2016-04-09',
              cycle_manager_appraisal_due: '2016-04-10',
              cycle_grade_due: '2016-04-11'
            },
            {
              id: '42135',
              cycle_name: 'Appraisal Cycle 5',
              cycle_is_active: true,
              cycle_type_id: '3',
              cycle_start_date: '2014-05-05',
              cycle_end_date: '2015-05-05',
              cycle_self_appraisal_due: '2016-05-12',
              cycle_manager_appraisal_due: '2016-05-13',
              cycle_grade_due: '2016-05-14'
            },
            {
              id: '42136',
              cycle_name: 'Appraisal Cycle 6',
              cycle_is_active: false,
              cycle_type_id: '1',
              cycle_start_date: '2014-06-06',
              cycle_end_date: '2015-06-06',
              cycle_self_appraisal_due: '2016-06-15',
              cycle_manager_appraisal_due: '2016-06-16',
              cycle_grade_due: '2016-06-17'
            },
            {
              id: '4217',
              cycle_name: 'Appraisal Cycle 7',
              cycle_is_active: false,
              cycle_type_id: '2',
              cycle_start_date: '2014-07-07',
              cycle_end_date: '2015-07-07',
              cycle_self_appraisal_due: '2016-07-18',
              cycle_manager_appraisal_due: '2016-07-19',
              cycle_grade_due: '2016-07-20'
            },
            {
              id: '42138',
              cycle_name: 'Appraisal Cycle 8',
              cycle_is_active: true,
              cycle_type_id: '1',
              cycle_start_date: '2014-08-08',
              cycle_end_date: '2015-08-08',
              cycle_self_appraisal_due: '2016-08-21',
              cycle_manager_appraisal_due: '2016-08-22',
              cycle_grade_due: '2016-08-23'
            },
            {
              id: '42139',
              cycle_name: 'Appraisal Cycle 9',
              cycle_is_active: true,
              cycle_type_id: '1',
              cycle_start_date: '2014-09-09',
              cycle_end_date: '2015-09-09',
              cycle_self_appraisal_due: '2016-09-24',
              cycle_manager_appraisal_due: '2016-09-25',
              cycle_grade_due: '2016-09-26'
            },
            {
              id: '421310',
              cycle_name: 'Appraisal Cycle 10',
              cycle_is_active: true,
              cycle_type_id: '4',
              cycle_start_date: '2014-10-10',
              cycle_end_date: '2015-10-10',
              cycle_self_appraisal_due: '2016-10-27',
              cycle_manager_appraisal_due: '2016-10-28',
              cycle_grade_due: '2016-10-29'
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
