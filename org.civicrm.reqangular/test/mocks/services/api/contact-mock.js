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
      leaveManagees: function () {
        return promiseResolvedWith(this.mockedContacts().list)
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
          total: 12,
          list: [
            {
              id: '1',
              contact_id: '1',
              display_name: 'Dr. Kiara Jensen-Parker',
              email: 'kh.jensen-parker@testing.info',
              contact_type: 'Individual',
            },
            {
              id: '2',
              contact_id: '2',
              display_name: 'jacobc82@lol.co.pl',
              email: 'cruz.v.jacob@spamalot.com',
              contact_type: 'Individual',
            },
            {
              id: '3',
              contact_id: '3',
              display_name: 'Mei Müller-Nielsen',
              email: 'mz.mller-nielsen75@infomail.co.pl',
              contact_type: 'Individual',
            },
            {
              id: '4',
              contact_id: '4',
              display_name: 'Ms. Brigette Deforest',
              email: 'deforest.p.brigette9@fakemail.info',
              contact_type: 'Individual',
            },
            {
              id: '5',
              contact_id: '5',
              display_name: 'robertsc@testing.net',
              email: 'clintr56@mymail.org',
              contact_type: 'Individual',
            },
            {
              id: '6',
              contact_id: '6',
              display_name: 'Dr. Allen Samuels Sr.',
              email: 'samuelsa@mymail.org',
              contact_type: 'Individual',
            },
            {
              id: '7',
              contact_id: '7',
              display_name: 'Mei Wilson',
              email: 'wilson.u.mei59@sample.org',
              contact_type: 'Individual',
            },
            {
              id: '8',
              contact_id: '8',
              display_name: 'Dr. Betty Díaz',
              email: 'dazb@sample.co.nz',
              contact_type: 'Individual',
            },
            {
              id: '9',
              contact_id: '9',
              display_name: 'Bob Jones-Dimitrov',
              email: 'bobj@testmail.biz',
              contact_type: 'Individual',
            },
            {
              id: '10',
              contact_id: '10',
              display_name: 'Truman Samuels III',
              email: 'trumans@spamalot.org',
              contact_type: 'Individual',
            },
            {
              id: '11',
              contact_id: '11',
              display_name: 'Iris Wagner',
              email: 'iwagner75@notmail.info',
              contact_type: 'Individual',
            },
            {
              id: '12',
              contact_id: '12',
              display_name: 'Mr. Rosario McReynolds',
              email: 'rosariomcreynolds@testmail.org',
              contact_type: 'Individual',
            },
            {
              id: '13',
              contact_id: '13',
              display_name: 'Shad Jones-Dimitrov III',
              email: 'shadj@lol.net',
              contact_type: 'Individual',
            },
            {
              id: '14',
              contact_id: '14',
              display_name: 'Toby Cruz Sr.',
              email: '',
              contact_type: 'Individual',
            },
            {
              id: '15',
              contact_id: '15',
              display_name: 'lareedaz61@testing.com',
              email: 'lareedaz61@testing.com',
              contact_type: 'Individual',
            },
            {
              id: '16',
              contact_id: '16',
              display_name: 'Dr. Jay Deforest',
              email: 'jayd@fishmail.org',
              contact_type: 'Individual',
            },
            {
              id: '17',
              contact_id: '17',
              display_name: 'Mr. Norris Cruz',
              email: 'norriscruz@spamalot.info',
              contact_type: 'Individual',
            },
            {
              id: '18',
              contact_id: '18',
              display_name: 'herminiac@fishmail.com',
              email: 'herminiac@fishmail.com',
              contact_type: 'Individual',
            },
            {
              id: '19',
              contact_id: '19',
              display_name: 'Ashley Terrell',
              email: 'terrell.ashley@testmail.info',
              contact_type: 'Individual',
            },
            {
              id: '20',
              contact_id: '20',
              display_name: 'Kandace Parker-Díaz',
              email: 'parker-daz.kandace67@fishmail.co.uk',
              contact_type: 'Individual',
            },
            {
              id: '21',
              contact_id: '21',
              display_name: 'Nicole Samuels',
              email: 'ng.samuels@testmail.net',
              contact_type: 'Individual',
            },
            {
              id: '22',
              contact_id: '22',
              display_name: 'Dr. Kandace Wattson',
              email: 'kandacewattson@testing.info',
              contact_type: 'Individual',
            },
            {
              id: '23',
              contact_id: '23',
              display_name: 'Brittney Cruz',
              email: 'cruz.brittney@spamalot.co.nz',
              contact_type: 'Individual',
            },
            {
              id: '24',
              contact_id: '24',
              display_name: 'Shauna Olsen',
              email: '',
              contact_type: 'Individual'
            },
            {
              "contact_id": "202",
              "contact_type": "Individual",
              "contact_sub_type": "",
              "sort_name": "civihr_staff@compucorp.co.uk",
              "display_name": "civihr_staff@compucorp.co.uk",
              "do_not_email": "0",
              "do_not_phone": "0",
              "do_not_mail": "0",
              "do_not_sms": "0",
              "do_not_trade": "0",
              "is_opt_out": "0",
              "legal_identifier": "",
              "external_identifier": "",
              "nick_name": "",
              "legal_name": "",
              "image_URL": "",
              "preferred_communication_method": "",
              "preferred_language": "en_US",
              "preferred_mail_format": "Both",
              "first_name": "",
              "middle_name": "",
              "last_name": "",
              "prefix_id": "",
              "suffix_id": "",
              "formal_title": "",
              "communication_style_id": "",
              "job_title": "",
              "gender_id": "",
              "birth_date": "",
              "is_deceased": "0",
              "deceased_date": "",
              "household_name": "",
              "organization_name": "",
              "sic_code": "",
              "contact_is_deleted": "0",
              "current_employer": "",
              "address_id": "",
              "street_address": "",
              "supplemental_address_1": "",
              "supplemental_address_2": "",
              "city": "",
              "postal_code_suffix": "",
              "postal_code": "",
              "geo_code_1": "",
              "geo_code_2": "",
              "state_province_id": "",
              "country_id": "",
              "phone_id": "",
              "phone_type_id": "",
              "phone": "",
              "email_id": "177",
              "email": "civihr_staff@compucorp.co.uk",
              "on_hold": "0",
              "im_id": "",
              "provider_id": "",
              "im": "",
              "worldregion_id": "",
              "world_region": "",
              "individual_prefix": "",
              "individual_suffix": "",
              "communication_style": "",
              "gender": "",
              "state_province_name": "",
              "state_province": "",
              "country": "",
              "id": "202"
            },
            {
              "contact_id": "203",
              "contact_type": "Individual",
              "contact_sub_type": "",
              "sort_name": "civihr_manager@compucorp.co.uk",
              "display_name": "civihr_manager@compucorp.co.uk",
              "do_not_email": "0",
              "do_not_phone": "0",
              "do_not_mail": "0",
              "do_not_sms": "0",
              "do_not_trade": "0",
              "is_opt_out": "0",
              "legal_identifier": "",
              "external_identifier": "",
              "nick_name": "",
              "legal_name": "",
              "image_URL": "",
              "preferred_communication_method": "",
              "preferred_language": "en_US",
              "preferred_mail_format": "Both",
              "first_name": "",
              "middle_name": "",
              "last_name": "",
              "prefix_id": "",
              "suffix_id": "",
              "formal_title": "",
              "communication_style_id": "",
              "job_title": "",
              "gender_id": "",
              "birth_date": "",
              "is_deceased": "0",
              "deceased_date": "",
              "household_name": "",
              "organization_name": "",
              "sic_code": "",
              "contact_is_deleted": "0",
              "current_employer": "",
              "address_id": "",
              "street_address": "",
              "supplemental_address_1": "",
              "supplemental_address_2": "",
              "city": "",
              "postal_code_suffix": "",
              "postal_code": "",
              "geo_code_1": "",
              "geo_code_2": "",
              "state_province_id": "",
              "country_id": "",
              "phone_id": "",
              "phone_type_id": "",
              "phone": "",
              "email_id": "178",
              "email": "civihr_manager@compucorp.co.uk",
              "on_hold": "0",
              "im_id": "",
              "provider_id": "",
              "im": "",
              "worldregion_id": "",
              "world_region": "",
              "individual_prefix": "",
              "individual_suffix": "",
              "communication_style": "",
              "gender": "",
              "state_province_name": "",
              "state_province": "",
              "country": "",
              "id": "203"
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
