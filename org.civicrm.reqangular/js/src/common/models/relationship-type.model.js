/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/models',
  'common/models/model',
  'common/models/instances/relationship-type.instance',
  'common/services/api/relationship-type'
], function (_, models) {
  'use strict';

  models.factory('RelationshipType', RelationshipType);

  RelationshipType.$inject = ['Model', 'RelationshipTypeAPI', 'RelationshipTypeInstance'];

  function RelationshipType (Model, RelationshipTypeAPI, RelationshipTypeInstance) {
    return Model.extend({
      /**
       * Returns a list of relationship types, each converted to a model instance
       *
       * @param  {Object}  filters - Values the full list should be filtered by
       * @param  {Object}  pagination - number of items to return per page and
       * the current page to fetch.
       * @param  {Number}  pagination.page - the current page to display.
       * @param  {Number}  pagination.size - the number of items per page.
       * @param  {String}  sort - The field and direction to order by.
       * @param  {Boolean} cache - Pass false if the request should not be cached.
       * @return {Promise} resolves to an object with the response's
       * record count and list of values.
       */
      all: function (filters, pagination, sort, cache) {
        return RelationshipTypeAPI.all(filters, pagination, sort, cache)
          .then(function (response) {
            response.list = response.list.map(function (record) {
              return RelationshipTypeInstance.init(record, true);
            });

            return response;
          });
      }
    });
  }
});
