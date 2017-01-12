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
                    total: 2,
                    list: [
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
