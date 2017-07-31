/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module'
], function (_, mocks) {
  'use strict';

  mocks.factory('api.group.mock', ['$q', function ($q) {
    return {
      all: function (filters, pagination, value) {
        var list, start, end;

        list = value || this.mockedGroups().list;

        if (filters) {
          list = list.filter(function (group) {
            return Object.keys(filters).every(function (key) {
              return group[key] === filters[key];
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
          allIds: list.map(function (group) {
            return group.id;
          }).join(',')
        });
      },
      find: function (id, value) {
        var group = value || this.mockedGroups().list.filter(function (group) {
          return group.id === id;
        })[0];

        return promiseResolvedWith(group);
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
             *
             * Mocked group
             */
      mockedGroups: function () {
        return {
          total: 5,
          list: [{
            id: '1',
            name: 'Administrators',
            title: 'Administrators',
            description: 'Contacts in this group are assigned Administrator role permissions.',
            is_active: '1',
            visibility: 'User and User Admin Only',
            group_type: ['1'],
            is_hidden: '0',
            is_reserved: '0'
          },
          {
            id: '2',
            name: 'Newsletter Subscribers',
            title: 'Newsletter Subscribers',
            is_active: '"1',
            visibility: 'Public Pages',
            where_clause: '...',
            select_tables: '...',
            where_tables: '...',
            group_type: ['1', '2'],
            is_hidden: '0',
            is_reserved: '0'
          },
          {
            id: '3',
            name: 'Summer Program Volunteers',
            title: 'Summer Program Volunteers',
            is_active: '1',
            visibility: 'Public Pages',
            where_clause: '...',
            select_tables: '...',
            where_tables: '...',
            group_type: ['1', '2'],
            is_hidden: '0',
            is_reserved: '0'
          },
          {
            id: '4',
            name: 'Advisory Board',
            title: 'Advisory Board',
            is_active: '1',
            visibility: 'Public Pages',
            where_clause: '...',
            select_tables: '...',
            where_tables: '...',
            group_type: ['1', '2'],
            is_hidden: '0',
            is_reserved: '0'
          },
          {
            id: '5',
            name: 'Case_Resources',
            title: 'Case Resources',
            description: 'Contacts in this group are listed with their phone number and email when viewing case. You also can send copies of case activities to these contacts.',
            is_active: '1',
            visibility: 'User and User Admin Only',
            where_clause: '...',
            select_tables: '...',
            where_tables: '...',
            group_type: '2',
            is_hidden: '0',
            is_reserved: '0'
          }]
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
