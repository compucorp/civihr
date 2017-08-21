/* eslint-env amd */

define([
  'common/modules/models',
  'common/models/model',
  'common/instances/contact-job-role-instance.instance',
  'common/services/api/contact-job-role-api.api'
], function (models) {
  'use strict';

  models.factory('ContactJobRole', [
    '$log', 'Model', 'ContactJobRoleAPI', 'ContactJobRoleInstance',
    function ($log, Model, ContactJobRoleAPI, ContactJobRoleInstance) {
      $log.debug('ContactJobRole');

      return Model.extend({
        /**
         * Calls the all() method of the Contact Job Role API,
         * and returns a Contact Job Role Instance for each contract.
         *
         * @param  {Object} params matches the api endpoint params
         * @return {Promise}
         */
        all: function (params) {
          return ContactJobRoleAPI.all(params)
            .then(function (contacts) {
              return contacts.list.map(function (contact) {
                return ContactJobRoleInstance.init(contact, true);
              });
            });
        }
      });
    }
  ]);
});
