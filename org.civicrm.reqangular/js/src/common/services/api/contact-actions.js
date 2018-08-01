define([
  'common/modules/apis',
  'common/services/api'
], function(apis) {
  'use strict';

  apis.factory('api.contactActions', ['$q', 'api', function($q, api) {
    /**
     * Fetches the possible 'refine search' values for the given field
     * @param  {string} entity The entity name
     * @param  {string} field  The field name to fetch options for
     * @return {Promise}       Resolves to the possible values array
     */
    function getRefineSearchOptions(entity, field) {
      return api.sendGET(entity, 'getoptions', {
        field: field,
        context: 'search'
      }).then(function(data) {
        return data.values;
      });
    }

    /**
     * Fetches the form fields for a group
     * @param  {string} groupId The group id to fetch the fields for
     * @return {Promise}        Resolves to the form fields array
     */
    function getFormFields(groupId) {
      return api.sendGET('UFField', 'get', {
        uf_group_id: groupId,
        is_active: true
      }).then(function(data) {
        return data.values;
      });
    }

    /**
     * Saves new contacts
     * @param  {string} contactType The type of the new contact
     * @param  {Object} formData    The data to be saved
     * @return {Promise}            Resolves to the saved data
     */
    function saveData(contactType, formData) {
      return $q(function(resolve, reject) {
        formData.contact_type = contactType;
        if (formData.email) {
          // The email field is a custom field, and has to be sent to the api
          // with the name 'custom_ID' (where ID is a number)
          return api.sendGET('CustomField', 'get', {
            return: ['id'],
            custom_group_id: 'Emergency_Contacts',
            name: 'email'
          }).then(function(data) {
            // Set the property name (with the correct ID), and delete the email property
            formData['custom_' + data.id] = formData.email;
            delete formData.email;
            resolve();
          });
        } else {
          resolve();
        }
      }).then(function() {
        return api.sendPOST('Contact', 'create', formData).then(function(data) {
          return data.values[0];
        });
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
        newIndividual: function(formData) {
          return saveData.call(this, 'Individual', formData);
        },
        newOrganization: function(formData) {
          return saveData.call(this, 'Organization', formData);
        },
        newHousehold: function(formData) {
          return saveData.call(this, 'Household', formData);
        }
      },

      getFormFields: {
        forNewIndividual: function() {
          return getFormFields.call(this, 'new_individual');
        },
        forNewOrganization: function() {
          return getFormFields.call(this, 'new_organization');
        },
        forNewHousehold: function() {
          return getFormFields.call(this, 'new_household');
        }
      }
    });
  }]);
});
