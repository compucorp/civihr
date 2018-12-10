/* eslint-env amd, jasmine */

define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('api.job-role', ['$log', 'api', function ($log, api) {
    $log.debug('api.jobRole');

    return api.extend({

      /**
       * Returns the list of job roles
       *
       * @NOTE API Service signature accepts `action` param,
       * but we do not seem to need it and pass `undefined` instead.
       *
       * @param  {Object} filters
       * @param  {Object} pagination
       * @param  {String} sort
       * @param  {Object} additionalParams
       * @param  {Boolean} cache
       * @return {Promise}
       */
      all: function (filters, pagination, sort, additionalParams, cache) {
        $log.debug('api.jobRole.api');

        return this.getAll('HrJobRoles',
          filters, pagination, sort, additionalParams, undefined, cache);
      },

      /**
       * Finds the job role with the given id
       *
       * @param {string/int} id
       * @return {Promise} resolves to the found contact
       */
      find: function (id) {
        $log.debug('api.jobRole.find');

        return this.sendGET('HRJobRole', 'get', { id: '' + id }, false)
          .then(function (data) {
            return data.values[0];
          });
      }
    });
  }]);
});
