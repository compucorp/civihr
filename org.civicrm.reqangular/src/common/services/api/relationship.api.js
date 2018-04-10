/* eslint-env amd */

define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('RelationshipAPI', RelationshipAPI);

  RelationshipAPI.$inject = ['$log', 'api'];

  function RelationshipAPI ($log, api) {
    $log.debug('RelationshipAPI');

    return api.extend({

      /**
       * Returns the list of relationships
       *
       * @param {object} filters
       * @param {object} pagination
       * @param {string} sort
       * @return {Promise}
       */
      all: function (filters, pagination, sort, additionalParams) {
        $log.debug('Relationship.all()');

        return this.getAll('Relationship', filters, pagination, sort, additionalParams);
      }
    });
  }
});
