/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/relationship.model',
  'common/models/instances/relationship.instance',
  'common/services/api/relationship.api'
], function (_) {
  'use strict';

  describe('Relationship', function () {
    var $q, $rootScope, Relationship, RelationshipAPI, RelationshipInstance;

    beforeEach(module('common.models', 'common.models.instances'));

    beforeEach(inject(function (_$q_, _$rootScope_, _Relationship_,
      _RelationshipAPI_, _RelationshipInstance_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      Relationship = _Relationship_;
      RelationshipAPI = _RelationshipAPI_;
      RelationshipInstance = _RelationshipInstance_;

      spyOn(RelationshipAPI, 'all').and.returnValue($q.resolve({
        list: [ { id: 123 } ]
      }));
    }));

    it('has the expected api', function () {
      expect(Object.keys(Relationship)).toEqual(['all']);
    });

    describe('all()', function () {
      var result;
      var filters = { key: 'filters' };
      var pagination = { key: 'pagination' };

      beforeEach(function () {
        Relationship.all(filters, pagination)
          .then(function (_result_) {
            result = _result_;
          });
        $rootScope.$digest();
      });

      describe('when requesting relationships', function () {
        it('passes the filters and paginations parameter to the API', function () {
          expect(RelationshipAPI.all).toHaveBeenCalledWith(filters, pagination);
        });
      });

      describe('when the result is ready', function () {
        var expectedInstance;

        beforeEach(function () {
          expectedInstance = RelationshipInstance.init({ id: 123 });
        });

        it('returns a list of relationships', function () {
          expect(result.list).toEqual([expectedInstance]);
        });

        it('initializes each relationship as a model instance', function () {
          expect(_.functions(result.list[0])).toEqual(_.functions(expectedInstance));
        });
      });
    });
  });
});
