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
      var instances = {};

      beforeEach(function () {
        _.forEach(relationshipData.named, function (relationship, relationshipName) {
          instances[relationshipName] = RelationshipInstance.init(relationship);
        });
      });

      it('returns true when the relationship is active', function () {
        expect(instances.isActive.isValid()).toBe(true);
      });

      it('returns false when the relationship is not active', function () {
        expect(instances.isNotActive.isValid()).toBe(false);
      });

      it('returns true when the relationship has a start date in the past', function () {
        expect(instances.hasStarted.isValid()).toBe(true);
      });

      it('returns false when the relationship has a start date in the future', function () {
        expect(instances.isInTheFuture.isValid()).toBe(false);
      });

      it('returns true when the relationship end date is in the future', function () {
        expect(instances.hasNotFinished.isValid()).toBe(true);
      });

      it('returns false when the relationship end date is in the past', function () {
        expect(instances.isInThePast.isValid()).toBe(false);
      });
    });
  });
});
