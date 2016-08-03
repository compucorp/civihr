define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('api.contactActions', ['api', function (api) {
    return api.extend({
      saveNewIndividual: function (firstName, lastName, email) {
        return this.sendPOST('Contact', 'create', {
          first_name: firstName,
          last_name: lastName,
          custom_100003: email,
          contact_type: 'Individual'
        }).then(function(data) {
          return data.values[0];
        });
      },
      saveNewOrganization: function (organizationName, email) {
        return this.sendPOST('Contact', 'create', {
          organization_name: organizationName,
          custom_100003: email,
          contact_type: 'Organization'
        }).then(function(data) {
          return data.values[0];
        });
      },
      saveNewHousehold: function (householdName, email) {
        return this.sendPOST('Contact', 'create', {
          household_name: householdName,
          custom_100003: email,
          contact_type: 'Household'
        }).then(function(data) {
          return data.values[0];
        });
      }
    });
  }]);
});
