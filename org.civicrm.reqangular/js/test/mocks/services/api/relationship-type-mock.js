/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module',
  'common/mocks/data/relationship-type.data'
], function (_, mocks, relationshipTypeData) {
  'use strict';

  mocks.factory('api.relationshipType.mock', ['$q', function ($q) {
    return {
      all: all,
      mockedRelationshipTypes: mockedRelationshipTypes,
      spyOnMethods: spyOnMethods
    };

    /**
     * Returns a promise that resolves to a list of mocked relationship types.
     *
     * @return {Promise} resolves to an array of objects.
     */
    function all () {
      var relationshipTypes = mockedRelationshipTypes();

      return $q.resolve({
        list: relationshipTypes,
        total: relationshipTypes.length
      });
    }

    /**
     * Returns mocked relationship types.
     *
     * @return {Array}
     */
    function mockedRelationshipTypes () {
      return _.cloneDeep(relationshipTypeData.all.values);
    }

    /**
     * Adds a spy on every method for testing purposes.
     */
    function spyOnMethods () {
      _.functions(this).forEach(function (method) {
        spyOn(this, method).and.callThrough();
      }.bind(this));
    }
  }]);
});
