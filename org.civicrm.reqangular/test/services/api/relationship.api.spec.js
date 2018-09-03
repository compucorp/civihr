/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/services/api/relationship.api'
], function () {
  'use strict';

  describe('RelationshipAPI', function () {
    var RelationshipAPI, $q, $rootScope;

    beforeEach(module('common.apis'));

    beforeEach(inject(function (_$q_, _$rootScope_, _RelationshipAPI_) {
      RelationshipAPI = _RelationshipAPI_;
      $q = _$q_;
      $rootScope = _$rootScope_;
    }));

    it('has expected interface', function () {
      expect(Object.keys(RelationshipAPI)).toContain('all');
    });

    describe('all()', function () {
      var result;
      var cache = false;
      var filters = { key: 'filters' };
      var mockResponse = { count: 1, values: [ { id: 2 } ] };
      var pagination = { key: 'pagination' };
      var sort = 'sort';

      beforeEach(function () {
        spyOn(RelationshipAPI, 'getAll').and.returnValue($q.resolve(mockResponse));
        RelationshipAPI.all(filters, pagination, sort, cache)
          .then(function (_result_) {
            result = _result_;
          });
        $rootScope.$apply();
      });

      it('calls getAll method', function () {
        expect(RelationshipAPI.getAll).toHaveBeenCalledWith(
          'Relationship', filters, pagination, sort, null, null, cache);
      });

      it('returns all the relationships', function () {
        expect(result).toEqual(mockResponse);
      });
    });
  });
});
