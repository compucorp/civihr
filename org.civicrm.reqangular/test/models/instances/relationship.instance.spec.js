/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/angularMocks',
  'common/models/instances/relationship.instance'
], function (_, moment) {
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
      var nextWeek = moment().add(7, 'day').format('YYYY-MM-DD');
      var previousWeek = moment().subtract(7, 'day').format('YYYY-MM-DD');
      var relationships = {
        isNotActive: { id: '1', is_active: '0' },
        isInThePast: { id: '2', is_active: '1', end_date: previousWeek },
        isInTheFuture: { id: '3', is_active: '1', start_date: nextWeek },
        isActive: { id: '4', is_active: '1' },
        hasStarted: { id: '5', is_active: '1', start_date: previousWeek },
        hasNotFinished: { id: '6', is_active: '1', end_date: nextWeek }
      };

      beforeEach(function () {
        _.forEach(relationships, function (relationship, index) {
          relationships[index] = RelationshipInstance.init(relationship);
        });
      });

      describe('when the relationship is active', function () {
        it('returns true', function () {
          expect(relationships.isActive.isValid()).toBe(true);
        });
      });

      describe('when the relationship is not active', function () {
        it('returns false', function () {
          expect(relationships.isNotActive.isValid()).toBe(false);
        });
      });

      describe('when the relationship has a start date in the past', function () {
        it('returns true', function () {
          expect(relationships.hasStarted.isValid()).toBe(true);
        });
      });

      describe('when the relationship has a start date in the future', function () {
        it('returns false', function () {
          expect(relationships.isInTheFuture.isValid()).toBe(false);
        });
      });

      describe('when the relationship end date is in the future', function () {
        it('returns true', function () {
          expect(relationships.hasNotFinished.isValid()).toBe(true);
        });
      });

      describe('when the relationship end date is in the past', function () {
        it('returns false', function () {
          expect(relationships.isInThePast.isValid()).toBe(false);
        });
      });
    });
  });
});
