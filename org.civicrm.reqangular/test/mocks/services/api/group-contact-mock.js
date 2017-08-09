/* eslint-env amd, jasmine */
define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/api/group-mock'
], function (_, mocks) {
  'use strict';

  mocks.factory('api.group-contact.mock', [
    '$q', 'api.contact.mock', 'api.group.mock',
    function ($q, contactAPI, groupAPI) {
      var mockedContacts = contactAPI.mockedContacts().list;
      var mockedGroups = groupAPI.mockedGroups().list;

      return {
        all: function (filters, pagination, value) {
          var list, start, end;

          list = value || this.mockedGroupsContacts.list;

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

                /**
                 * Adds a spy on every method for testing purposes
                 */
        spyOnMethods: function () {
          _.functions(this).forEach(function (method) {
            spyOn(this, method).and.callThrough();
          }.bind(this));
        },

                /**
                 * Mocked group contacts
                 */
        mockedGroupsContacts: {
          total: mockedContacts.length,
          list: (function () {
            var id = 1;

            return mockedContacts.map(function (contact) {
              return {
                id: '' + id++,
                group_id: randomValue(mockedGroups).id,
                contact_id: contact.id,
                status: 'Added'
              };
            });
          }())
        }
      };

            /**
             * Pick a random value out of a collection
             *
             * @param {array} collection
             * @return {object}
             */
      function randomValue (collection) {
        return _.sample(collection);
      }

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
    }
  ]);
});
