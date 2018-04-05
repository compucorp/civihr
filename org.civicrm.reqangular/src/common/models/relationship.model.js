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
       * @param {Object} filters - Values the full list should be filtered by
       * @param {Object} pagination
       *   `page` for the current page, `size` for number of items per page
       * @return {Promise}
       */
      all: function (filters, pagination) {
        return RelationshipAPI.all(filters, pagination).then(function (response) {
          response.list = response.list.map(function (relationship) {
            return RelationshipInstance.init(relationship, true);
          });

          return response;
        });
      },

      /**
       * Only returns relationships that are valid.
       *
       * @param {Object} filters - filter values to pass to the API.
       * @param {Object} pagination - number of items to return per page and
       * the current page to fetch.
       * @return {Promise}
       */
      allValid: function (filters, pagination) {
        filters = _.defaults(filters || {}, {
          'relationship_type_id.is_active': 1
        });

        return this.all(filters, pagination)
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
