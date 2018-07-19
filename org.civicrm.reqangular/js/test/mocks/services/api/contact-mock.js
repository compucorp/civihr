/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/data/contact.data'
], function (_, mocks, ContactData) {
  'use strict';

  mocks.factory('api.contact.mock', ['$q', function ($q) {
    return {
      all: function (filters, pagination, sort, additionalParam, value) {
        var list, start, end;

        list = value || ContactData.all.values;

        if (filters) {
          list = list.filter(function (contact) {
            return Object.keys(filters).every(function (key) {
              if (filters[key] === null) {
                return true;
              } else if (key === 'display_name') {
                return (new RegExp(filters[key], 'i')).test(contact[key]);
              } else if (filters[key].IN) {
                return _.includes(filters[key].IN, contact[key]);
              } else {
                return contact[key] === filters[key];
              }
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
          allIds: list.map(function (contact) {
            return contact.id;
          }).join(',')
        });
      },
      find: function (id, value) {
        var contact = value || ContactData.all.values.filter(function (contact) {
          return contact.id === id;
        })[0];

        return promiseResolvedWith(contact);
      },
      leaveManagees: function () {
        return promiseResolvedWith(this.mockedContacts().list);
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
       * Mocked contacts
       *
       * @todo remove this method completely and use `common/mocks/data/contact.data`
       * directly in modules that were previously using .mockedContacts()
       */
      mockedContacts: function () {
        var contacts = ContactData.all.values;

        return {
          total: contacts.length,
          list: contacts.map(function (mockedContact) {
            return mockedContact;
          })
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
