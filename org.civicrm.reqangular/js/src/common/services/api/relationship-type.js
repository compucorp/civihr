/* eslint-env amd */

define([
  'common/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('RelationshipTypeAPI', RelationshipTypeAPI);

  RelationshipTypeAPI.$inject = ['$log', 'api'];

  function RelationshipTypeAPI ($log, api) {
    $log.debug('RelationshipAPI');

    return api.extend({

      /**
       * Returns the list of relationship types
       *
       * @param  {Object}  filters
       * @param  {Object}  pagination
       * @param  {String}  sort
       * @param  {Boolean} cache
       * @return {Promise}
       */
      all: function (filters, pagination, sort, cache) {
        $log.debug('RelationshipType.all()');

        return this.getAll('RelationshipType', filters, pagination, sort, null, null, cache);
      }
    });
  }
});
