/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/relationship.data',
  'common/angularMocks',
  'common/models/relationship.model',
  'common/models/instances/relationship.instance',
  'common/services/api/relationship.api'
], function (_, relationshipData) {
  'use strict';

  describe('RelationshipModel', function () {
    var $q, $rootScope, RelationshipModel, RelationshipAPI, RelationshipInstance,
      result;
    var filters = { key: 'filters' };
    var pagination = { key: 'pagination' };

    beforeEach(module('common.models', 'common.models.instances'));

    beforeEach(inject(function (_$q_, _$rootScope_,
      _RelationshipAPI_, _RelationshipInstance_, _RelationshipModel_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      RelationshipAPI = _RelationshipAPI_;
      RelationshipInstance = _RelationshipInstance_;
      RelationshipModel = _RelationshipModel_;

      spyOn(RelationshipAPI, 'all').and.returnValue($q.resolve({
        list: relationshipData.all.values
      }));
    }));

    it('has the expected api', function () {
      expect(Object.keys(RelationshipModel)).toEqual(['all', 'allValid']);
    });

    describe('all()', function () {
      beforeEach(function () {
        RelationshipModel.all(filters, pagination)
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
        var expectedInstances;

        beforeEach(function () {
          expectedInstances = _.map(relationshipData.all.values, function (relationship) {
            return RelationshipInstance.init(relationship, true);
          });
        });

        it('returns a list of relationships', function () {
          expect(result.list).toEqual(expectedInstances);
        });

        it('initializes each relationship as a model instance', function () {
          expect(_.functions(result.list[0])).toEqual(_.functions(expectedInstances[0]));
        });
      });
    });

    describe('allValid()', function () {
      var allValidRelationships;

      beforeEach(function () {
        spyOn(RelationshipModel, 'all').and.callThrough();

        allValidRelationships = relationshipData.all.values
          .filter(function (relationship) {
            return RelationshipInstance.init(relationship, true).isValid();
          });

        RelationshipModel.allValid(filters, pagination)
          .then(function (_result_) {
            result = _result_;
          });
        $rootScope.$digest();
      });

      it('passes the filters and pagination parameters to the all method', function () {
        expect(RelationshipModel.all).toHaveBeenCalledWith(filters, pagination);
      });

      it('only returns relationships where the relationship type is active', function () {
        expect(RelationshipModel.all.calls.argsFor(0)[0]).toEqual(jasmine.objectContaining({
          'relationship_type_id.is_active': 1
        }));
      });

      it('only returns valid relationships', function () {
        expect(result.list).toEqual(allValidRelationships);
      });
    });
  });
});
