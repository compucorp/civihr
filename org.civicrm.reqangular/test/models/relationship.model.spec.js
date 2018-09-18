/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/relationship.data',
  'common/angularMocks',
  'common/mocks/services/api/relationship.api.mock',
  'common/models/relationship.model',
  'common/models/instances/relationship.instance',
  'common/services/api/relationship.api'
], function (_, relationshipData) {
  'use strict';

  describe('RelationshipModel', function () {
    var $provide, $rootScope, RelationshipModel, RelationshipAPI,
      RelationshipInstance, result;
    var cache = false;
    var filters = {};
    var pagination = { key: 'pagination' };
    var sort = 'sort ASC';

    beforeEach(function () {
      module('common.models', 'common.models.instances', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });

      inject(function (RelationshipAPIMock) {
        $provide.value('RelationshipAPI', RelationshipAPIMock);
      });
    });

    beforeEach(inject(function (_$rootScope_,
      _RelationshipAPI_, _RelationshipInstance_, _RelationshipModel_) {
      $rootScope = _$rootScope_;
      RelationshipAPI = _RelationshipAPI_;
      RelationshipInstance = _RelationshipInstance_;
      RelationshipModel = _RelationshipModel_;

      RelationshipAPI.spyOnMethods();
    }));

    it('has the expected api', function () {
      expect(Object.keys(RelationshipModel)).toEqual(['all', 'allValid']);
    });

    describe('all()', function () {
      beforeEach(function (done) {
        RelationshipModel.all(filters, pagination, sort, cache)
          .then(function (_result_) {
            result = _result_;
          }).finally(done);
        $rootScope.$digest();
      });

      describe('when requesting relationships', function () {
        it('passes the filters and paginations parameter to the API', function () {
          expect(RelationshipAPI.all).toHaveBeenCalledWith(filters, pagination, sort, cache);
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

      beforeEach(function (done) {
        spyOn(RelationshipModel, 'all').and.callThrough();

        allValidRelationships = relationshipData.all.values
          .filter(function (relationship) {
            return RelationshipInstance.init(relationship, true).isValid();
          });

        RelationshipModel.allValid(filters, pagination, sort, cache)
          .then(function (_result_) {
            result = _result_;
          }).finally(done);
        $rootScope.$digest();
      });

      it('passes the filters and pagination parameters to the all method', function () {
        expect(RelationshipModel.all).toHaveBeenCalledWith(filters, pagination, sort, cache);
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
