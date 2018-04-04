/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/relationship.data',
  'common/angularMocks',
  'common/models/instances/relationship.instance'
], function (_, relationshipData) {
  'use strict';

  describe('RelationshipInstance', function () {
    var ModelInstance, RelationshipInstance;

    beforeEach(module('common.models.instances'));
    beforeEach(inject(function (_ModelInstance_, _RelationshipInstance_) {
      ModelInstance = _ModelInstance_;
      RelationshipInstance = _RelationshipInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(RelationshipInstance))
        .toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('isValid()', function () {
      beforeEach(function () {
        _.forEach(relationshipData.named, function (relationship, index) {
          relationshipData.named[index] = RelationshipInstance.init(relationship);
        });
      });

      it('returns true when the relationship is active', function () {
        expect(relationshipData.named.isActive.isValid()).toBe(true);
      });

      it('returns false when the relationship is not active', function () {
        expect(relationshipData.named.isNotActive.isValid()).toBe(false);
      });

      it('returns true when the relationship has a start date in the past', function () {
        expect(relationshipData.named.hasStarted.isValid()).toBe(true);
      });

      it('returns false when the relationship has a start date in the future', function () {
        expect(relationshipData.named.isInTheFuture.isValid()).toBe(false);
      });

      it('returns true when the relationship end date is in the future', function () {
        expect(relationshipData.named.hasNotFinished.isValid()).toBe(true);
      });

      it('returns false when the relationship end date is in the past', function () {
        expect(relationshipData.named.isInThePast.isValid()).toBe(false);
      });
    });
  });
});
