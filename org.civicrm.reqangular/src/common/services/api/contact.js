define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('api.contact', ['$log', 'api', function ($log, api) {
    $log.debug('api.contact');

    return api.extend({

      /**
       * Returns the list of contacts
       *
       * @param {object} filters
       * @param {object} pagination
       * @param {string} sort
       * @param {object} additionalParams
       * @return {Promise}
       */
      all: function (filters, pagination, sort, additionalParams) {
        $log.debug('api.contact.api');

        return this.getAll('Contact', filters, pagination, sort, additionalParams);
      },

      /**
       * Finds the contact with the given id
       *
       * @param {string/int} id
       * @return {Promise} resolves to the found contact
       */
      find: function (id) {
        $log.debug('api.contact.find');

        return this.sendGET('Contact', 'get', { id: '' + id }, false)
          .then(function (data) {
            return data.values[0];
          });
      },

      /**
       * Finds the contacts who are managed by sent contact id
       *
       * @return {Promise} resolves to the found contact
       */
      leaveManagees: function (managedBy, params) {
        $log.debug('api.contact.leaveManagees');

        params = _.assign({}, params, {
          managed_by: managedBy
        });

        return this.sendGET('Contact', 'getleavemanagees', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
