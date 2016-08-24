define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('api.contactActions', ['api', function (api) {
    /**
    * Fetches the possible "refine search" values for the given field
    * @param  {string} entity The entity name
    * @param  {string} field  The field name to fetch options for
    * @return {Promise}       Resolves to the possible values
    */
    function getRefineSearchOptions(entity, field) {
      return this.sendGET(entity, 'getoptions', {
        field: field,
        context: 'search'
      }).then(function(data) {
        return data.values;
      });
    }

    return api.extend({
      getOptions: {
        forContactType: function() {
          return getRefineSearchOptions.call(this, 'Contact', 'contact_type');
        },
        forGroup: function() {
          return getRefineSearchOptions.call(this, 'GroupContact', 'group_id');
        },
        forTag: function() {
          return getRefineSearchOptions.call(this, 'EntityTag', 'tag_id');
        },
        forStateProvince: function() {
          return getRefineSearchOptions.call(this, 'Address', 'state_province_id');
        },
        forCountry: function() {
          return getRefineSearchOptions.call(this, 'Address', 'country_id');
        },
        forGender: function() {
          return getRefineSearchOptions.call(this, 'Contact', 'gender_id');
        },
        forDeceased: function() {
          return getRefineSearchOptions.call(this, 'Contact', 'is_deceased');
        }
      },

      save: {
        newIndividual: function (firstName, lastName, email) {
          return this.sendPOST('Contact', 'create', {
            first_name: firstName,
            last_name: lastName,
            custom_100003: email,
            contact_type: 'Individual'
          }).then(function(data) {
            return data.values[0];
          });
        },
        newOrganization: function (organizationName, email) {
          return this.sendPOST('Contact', 'create', {
            organization_name: organizationName,
            custom_100003: email,
            contact_type: 'Organization'
          }).then(function(data) {
            return data.values[0];
          });
        },
        newHousehold: function (householdName, email) {
          return this.sendPOST('Contact', 'create', {
            household_name: householdName,
            custom_100003: email,
            contact_type: 'Household'
          }).then(function(data) {
            return data.values[0];
          });
        }
      }
    });
  }]);
});
