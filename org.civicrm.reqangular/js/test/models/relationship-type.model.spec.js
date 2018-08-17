/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/relationship-type.model',
  'common/mocks/services/api/relationship-type-mock'
], function (_) {
  'use strict';

  describe('RelationshipType', function () {
    var $provide, $rootScope, RelationshipType,
      relationshipTypeApi, relationshipTypeApiMock;

    beforeEach(module('common.models', 'common.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(['api.relationshipType.mock', function (_relationshipTypeApiMock_) {
      relationshipTypeApiMock = _relationshipTypeApiMock_;

      $provide.value('RelationshipTypeAPI', relationshipTypeApiMock);
    }]));

    beforeEach(inject([
      '$rootScope', 'RelationshipType', 'RelationshipTypeAPI',
      function (_$rootScope_, _RelationshipType_, _relationshipTypeApi_) {
        $rootScope = _$rootScope_;
        RelationshipType = _RelationshipType_;
        relationshipTypeApi = _relationshipTypeApi_;

        relationshipTypeApi.spyOnMethods();
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(RelationshipType)).toEqual(['all']);
    });

    describe('all()', function () {
      var relationshipTypes;
      var cache = false;
      var filters = { id: _.uniqueId() };
      var pagination = { page: 1, size: 10 };
      var sort = 'id DESC';

      beforeEach(function () {
        RelationshipType.all(filters, pagination, sort, cache)
          .then(function (relationshipTypeResponse) {
            relationshipTypes = relationshipTypeResponse.list;
          });

        $rootScope.$digest();
      });

      it('returns model instances', function () {
        expect(relationshipTypes.every(function (modelInstance) {
          return 'init' in modelInstance;
        })).toBe(true);
      });

      it('calls corresponding API methods with expected params', function () {
        expect(relationshipTypeApi.all).toHaveBeenCalledWith(filters, pagination, sort, cache);
      });
    });
  });
});
