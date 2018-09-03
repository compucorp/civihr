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
       * @param  {Object}  filters
       * @param  {Object}  pagination
       * @param  {String}  sort
       * @param  {Boolean} cache
       * @return {Promise}
       */
      all: function (filters, pagination, sort, cache) {
        $log.debug('Relationship.all()');

        return this.getAll('Relationship', filters, pagination, sort, null, null, cache);
      }
    });
  }
});
