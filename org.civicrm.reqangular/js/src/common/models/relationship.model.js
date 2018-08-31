/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/models',
  'common/models/model',
  'common/models/instances/relationship.instance',
  'common/services/api/relationship.api'
], function (_, models) {
  'use strict';

  models.factory('RelationshipModel', Relationship);

  Relationship.$inject = ['Model', 'RelationshipAPI', 'RelationshipInstance'];

  function Relationship (Model, RelationshipAPI, RelationshipInstance) {
    return Model.extend({
      /**
       * Returns a list of relationships, each converted to a model instance
       *
       * @param  {Object}  filters - Values the full list should be filtered by
       * @param  {Object}  pagination - number of items to return per page and
       * the current page to fetch.
       * @param  {Number}  pagination.page - the current page to display.
       * @param  {Number}  pagination.size - the number of items per page.
       * @param  {String}  sort - The field and direction to order by.
       * @param  {Boolean} cache - Pass false if the request should not be cached.
       * @return {Promise} resolves to the an object with the relationship's response
       * count and list of values.
       */
      all: function (filters, pagination, sort, cache) {
        return RelationshipAPI.all(filters, pagination, sort, cache)
          .then(function (response) {
            response.list = response.list.map(function (relationship) {
              return RelationshipInstance.init(relationship, true);
            });

            return response;
          });
      },

      /**
       * Only returns relationships that are valid.
       *
       * @param  {Object}  filters - filter values to pass to the API.
       * @param  {Object}  pagination - number of items to return per page and
       * the current page to fetch.
       * @param  {Number}  pagination.page - the current page to display.
       * @param  {Number}  pagination.size - the number of items per page.
       * @param  {String}  sort - The field and direction to order by.
       * @param  {Boolean} cache - Pass false if the request should not be cached.
       * @return {Promise} resolves to the an object with the relationship's response
       * count and list of values.
       */
      allValid: function (filters, pagination, sort, cache) {
        filters = _.defaults(filters || {}, {
          'relationship_type_id.is_active': 1
        });

        return this.all(filters, pagination, sort, cache)
          .then(function (result) {
            result.list = result.list.filter(function (relationship) {
              return relationship.isValid();
            });

            return result;
          });
      }
    });
  }
});
