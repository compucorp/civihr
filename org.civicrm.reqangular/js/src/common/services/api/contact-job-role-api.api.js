/* eslint-env amd */

define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('ContactJobRoleAPI', ['$log', 'api', function ($log, api) {
    $log.debug('ContactJobRoleAPI');

    return api.extend({

      /**
       * Returns the list of contact job roles
       *
       * @param {object} filters
       * @param {object} pagination
       * @param {string} sort
       * @return {Promise}
       */
      all: function (filters, pagination, sort, additionalParams) {
        $log.debug('ContactJobRoleAPI.all()');

        return this.getAll('ContactHrJobRoles', filters, pagination, sort, additionalParams);
      }
    });
  }]);
});
