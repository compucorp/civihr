/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/services/api/relationship-type',
  'common/mocks/services/api/relationship-type-mock'
], function (_) {
  'use strict';

  describe('RelationshipTypeAPI', function () {
    var RelationshipTypeAPI, $rootScope, RelationshipTypeAPIMock, $q;

    beforeEach(module('common.apis', 'common.mocks'));

    beforeEach(inject(['RelationshipTypeAPI', 'api.relationshipType.mock', '$rootScope', '$q', function (_RelationshipTypeAPI_, _RelationshipTypeAPIMock_, _$rootScope_, _$q_) {
      RelationshipTypeAPI = _RelationshipTypeAPI_;
      RelationshipTypeAPIMock = _RelationshipTypeAPIMock_;
      $rootScope = _$rootScope_;
      $q = _$q_;
    }]));

    it('has expected interface', function () {
      expect(Object.keys(RelationshipTypeAPI)).toContain('all');
    });

    describe('all()', function () {
      var RelationshipTypeAPIPromise;
      var filters = { key: 'filters' };
      var pagination = { key: 'pagination' };
      var sort = 'sort';
      var cache = true;

      beforeEach(function () {
        spyOn(RelationshipTypeAPI, 'getAll').and.returnValue($q.resolve(RelationshipTypeAPIMock.mockedRelationshipTypes()));
        RelationshipTypeAPIPromise = RelationshipTypeAPI.all(filters, pagination, sort, cache);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns all the relationship types', function () {
        RelationshipTypeAPIPromise.then(function (result) {
          expect(result).toEqual(RelationshipTypeAPIMock.mockedRelationshipTypes());
        });
      });

      it('calls getAll method with expected params', function () {
        expect(RelationshipTypeAPI.getAll).toHaveBeenCalledWith('RelationshipType', filters, pagination, sort, null, null, cache);
      });
    });
  });
});
