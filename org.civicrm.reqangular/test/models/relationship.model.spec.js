/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/relationship.model',
  'common/models/instances/relationship.instance',
  'common/services/api/relationship.api'
], function (_) {
  'use strict';

  describe('RelationshipModel', function () {
    var $q, $rootScope, RelationshipModel, RelationshipAPI, RelationshipInstance;

    beforeEach(module('common.models', 'common.models.instances'));

    beforeEach(inject(function (_$q_, _$rootScope_,
      _RelationshipAPI_, _RelationshipInstance_, _RelationshipModel_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      RelationshipAPI = _RelationshipAPI_;
      RelationshipInstance = _RelationshipInstance_;
      RelationshipModel = _RelationshipModel_;

      spyOn(RelationshipAPI, 'all').and.returnValue($q.resolve({
        list: [ { id: 123 } ]
      }));
    }));

    it('has the expected api', function () {
      expect(Object.keys(RelationshipModel)).toEqual(['all', 'allValid']);
    });

    describe('all()', function () {
      var result;
      var filters = { key: 'filters' };
      var pagination = { key: 'pagination' };

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

    describe('allValid()', function () {

    });
  });
});
