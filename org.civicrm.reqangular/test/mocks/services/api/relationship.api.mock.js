/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/data/relationship.data',
  'common/models/instances/relationship.instance'
], function (_, mocks, relationshipData) {
  'use strict';

  mocks.factory('RelationshipAPIMock', RelationshipAPIMock);

  RelationshipAPIMock.$inject = ['$q', 'RelationshipInstance'];

  function RelationshipAPIMock ($q, RelationshipInstance) {
    return {
      /**
       * returns a list of relationships.
       *
       * @return {object}
       */
      all: function (filters) {
        var result = _.clone(relationshipData.all);
        filters = _.clone(filters || {});

        delete filters['relationship_type_id.is_active'];

        result.list = _.chain(result.values).filter(filters)
          .map(function (relationship) {
            return RelationshipInstance.init(relationship);
          }).value();
        result.count = result.list.length;

        delete result.values;

        return $q.resolve(result);
      },

      /**
       * Adds a spy on every method for testing purposes
       */
      spyOnMethods: function () {
        _.functions(this).forEach(function (method) {
          spyOn(this, method).and.callThrough();
        }.bind(this));
      }
    };
  }
});
