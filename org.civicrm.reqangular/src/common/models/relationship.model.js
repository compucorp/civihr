/* eslint-env amd */

define([
  'common/modules/models',
  'common/models/model',
  'common/models/instances/relationship.instance',
  'common/services/api/relationship.api'
], function (models) {
  'use strict';

  models.factory('RelationshipModel', Relationship);

  Relationship.$inject = ['Model', 'RelationshipAPI', 'RelationshipInstance'];

  function Relationship (Model, RelationshipAPI, RelationshipInstance) {
    return Model.extend({
      /**
       * Returns a list of relationships, each converted to a model instance
       *
       * @param {object} filters - Values the full list should be filtered by
       * @param {object} pagination
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
      }
    });
  }
});
