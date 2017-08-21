/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/api/option-group-mock'
], function (_, mocks) {
  'use strict';

  mocks.factory('ContactJobRoleAPIMock', [
    '$q', 'api.contact.mock', 'api.optionGroup.mock',
    function ($q, contactAPI, optionGroupAPI) {
      var mockedContacts = contactAPI.mockedContacts().list;
      var mockedOptionValues = optionGroupAPI.mockedOptionValues();

      return {
        all: function (filters, pagination, value) {
          var list, start, end;

          list = value || this.mockedContactJobRoles.list;

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

          return $q.resolve({ list: list });
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
         * Mocked Contact Job Roles
         */
        mockedContactJobRoles: {
          list: (function () {
            var i = 0;

            return mockedContacts.map(function (contact) {
              i += Math.ceil(Math.random() * 100);

              return {
                id: '' + i,
                title: 'Job Role #' + i,
                department: _.sample(mockedOptionValues['hrjc_department']).id,
                level_type: _.sample(mockedOptionValues['hrjc_level_type']).id,
                location: _.sample(mockedOptionValues['hrjc_location']).id,
                contact_id: contact.id
              };
            });
          }())
        }
      };
    }
  ]);
});
