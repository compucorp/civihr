/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/api/option-group-mock'
], function (_, mocks) {
  'use strict';

  mocks.factory('api.job-role.mock', [
    '$q', 'api.contact.mock', 'api.optionGroup.mock',
    function ($q, contactAPI, optionGroupAPI) {
      var mockedContacts = contactAPI.mockedContacts().list;
      var mockedOptionValues = optionGroupAPI.mockedOptionValues();

      return {
        all: function (filters, pagination, value) {
          var list, start, end;

          list = value || this.mockedJobRoles.list;

          if (filters) {
            list = list.filter(function (jobRole) {
              return Object.keys(filters).every(function (key) {
                return jobRole[key] === filters[key];
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
            allIds: list.map(function (jobRole) {
              return jobRole.id;
            }).join(',')
          });
        },
        find: function (id, value) {
          var jobRole = value || this.mockedJobRoles.list.filter(function (jobRole) {
            return jobRole.id === id;
          })[0];

          return promiseResolvedWith(jobRole);
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
             * Mocked Job Roles
             */
        mockedJobRoles: {
          total: 10,
          list: (function () {
            var i = 0;

            function dateFromId (i) {
              i = i < 10 ? ('0' + i) : i;
              return '20' + i + '-' + i + '-' + i + ' 00:00:00';
            }

                    // Create a job role for each mocked contact
            return mockedContacts.map(function (contact) {
              i++;

              return {
                id: '' + i,
                job_contract_id: '' + i,
                title: 'Job Role #' + i,
                description: 'Description for Job Role #' + i,
                department: randomValue(mockedOptionValues, 'hrjc_department').id,
                level_type: randomValue(mockedOptionValues, 'hrjc_level_type').id,
                location: randomValue(mockedOptionValues, 'hrjc_location').id,
                region: randomValue(mockedOptionValues, 'hrjc_region').id,
                start_date: dateFromId(i),
                end_date: dateFromId(i + 1),
                'api.HRJobContract.getsingle': {
                  id: i,
                  contact_id: contact.id,
                  is_primary: '1'
                }
              };
            });
          }())
        }
      };

        /**
         * Pick a random value out of a collection
         *
         * @param {array} collection
         * @param {string} key - The sub-collection key
         * @return {object}
         */
      function randomValue (collection, key) {
        return _.sample(collection[key]);
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
    }]);
});
