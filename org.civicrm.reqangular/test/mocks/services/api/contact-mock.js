define([
  'common/lodash',
  'common/mocks/module'
], function (_, mocks) {
  'use strict';

  mocks.factory('api.contact.mock', ['$q', function ($q) {

    return {
      all: function (filters, pagination, value) {
        var list, start, end;

        list = value || this.mockedContacts().list;

        if (filters) {
          list = list.filter(function (contact) {
            return Object.keys(filters).every(function (key) {
              if (key === 'display_name') {
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
        })
      },
      find: function (id, value) {
        var contact = value || this.mockedContacts().list.filter(function (contact) {
            return contact.id === id;
          })[0];

        return promiseResolvedWith(contact);
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
       * Mocked contacts
       */
      mockedContacts: function () {
        return {
          total: 10,
          list: [
            {
              id: '1',
              display_name: 'Dr. Kiara Jensen-Parker',
              email: 'kh.jensen-parker@testing.info',
              contact_type: 'Individual',
            },
            {
              id: '2',
              display_name: 'jacobc82@lol.co.pl',
              email: 'cruz.v.jacob@spamalot.com',
              contact_type: 'Individual',
            },
            {
              id: '3',
              display_name: 'Mei Müller-Nielsen',
              email: 'mz.mller-nielsen75@infomail.co.pl',
              contact_type: 'Individual',
            },
            {
              id: '4',
              display_name: 'Ms. Brigette Deforest',
              email: 'deforest.p.brigette9@fakemail.info',
              contact_type: 'Individual',
            },
            {
              id: '5',
              display_name: 'robertsc@testing.net',
              email: 'clintr56@mymail.org',
              contact_type: 'Individual',
            },
            {
              id: '6',
              display_name: 'Dr. Allen Samuels Sr.',
              email: 'samuelsa@mymail.org',
              contact_type: 'Individual',
            },
            {
              id: '7',
              display_name: 'Mei Wilson',
              email: 'wilson.u.mei59@sample.org',
              contact_type: 'Individual',
            },
            {
              id: '8',
              display_name: 'Dr. Betty Díaz',
              email: 'dazb@sample.co.nz',
              contact_type: 'Individual',
            },
            {
              id: '9',
              display_name: 'Bob Jones-Dimitrov',
              email: 'bobj@testmail.biz',
              contact_type: 'Individual',
            },
            {
              id: '10',
              display_name: 'Truman Samuels III',
              email: 'trumans@spamalot.org',
              contact_type: 'Individual',
            },
            {
              id: '11',
              display_name: 'Iris Wagner',
              email: 'iwagner75@notmail.info',
              contact_type: 'Individual',
            },
            {
              id: '12',
              display_name: 'Mr. Rosario McReynolds',
              email: 'rosariomcreynolds@testmail.org',
              contact_type: 'Individual',
            },
            {
              id: '13',
              display_name: 'Shad Jones-Dimitrov III',
              email: 'shadj@lol.net',
              contact_type: 'Individual',
            },
            {
              id: '14',
              display_name: 'Toby Cruz Sr.',
              email: '',
              contact_type: 'Individual',
            },
            {
              id: '15',
              display_name: 'lareedaz61@testing.com',
              email: 'lareedaz61@testing.com',
              contact_type: 'Individual',
            },
            {
              id: '16',
              display_name: 'Dr. Jay Deforest',
              email: 'jayd@fishmail.org',
              contact_type: 'Individual',
            },
            {
              id: '17',
              display_name: 'Mr. Norris Cruz',
              email: 'norriscruz@spamalot.info',
              contact_type: 'Individual',
            },
            {
              id: '18',
              display_name: 'herminiac@fishmail.com',
              email: 'herminiac@fishmail.com',
              contact_type: 'Individual',
            },
            {
              id: '19',
              display_name: 'Ashley Terrell',
              email: 'terrell.ashley@testmail.info',
              contact_type: 'Individual',
            },
            {
              id: '20',
              display_name: 'Kandace Parker-Díaz',
              email: 'parker-daz.kandace67@fishmail.co.uk',
              contact_type: 'Individual',
            },
            {
              id: '21',
              display_name: 'Nicole Samuels',
              email: 'ng.samuels@testmail.net',
              contact_type: 'Individual',
            },
            {
              id: '22',
              display_name: 'Dr. Kandace Wattson',
              email: 'kandacewattson@testing.info',
              contact_type: 'Individual',
            },
            {
              id: '23',
              display_name: 'Brittney Cruz',
              email: 'cruz.brittney@spamalot.co.nz',
              contact_type: 'Individual',
            },
            {
              id: '24',
              display_name: 'Shauna Olsen',
              email: '',
              contact_type: 'Individual'
            }
          ]
        };
      }
    }

    /**
     * Returns a promise that will resolve with the given value
     *
     * @param {any} value
     * @return {Promise}
     */
    function promiseResolvedWith(value) {
      var deferred = $q.defer();
      deferred.resolve(value);

      return deferred.promise;
    }
  }]);
});
