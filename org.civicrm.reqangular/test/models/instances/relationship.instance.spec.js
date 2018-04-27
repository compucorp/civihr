/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/mocks/data/relationship.data',
  'common/angularMocks',
  'common/models/instances/relationship.instance'
], function (_, moment, relationshipData) {
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
      var instance;
      var nextWeek = moment().add(7, 'day').format('YYYY-MM-DD');
      var previousWeek = moment().subtract(7, 'day').format('YYYY-MM-DD');
      var today = moment().format('YYYY-MM-DD');

      describe('when the relationship is active and neither the start date nor end date are defined', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1'
          });
        });

        it('returns true when the relationship is active', function () {
          expect(instance.isValid()).toBe(true);
        });
      });

      describe('when the relationship is not active', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '0'
          });
        });

        it('returns false', function () {
          expect(instance.isValid()).toBe(false);
        });
      });

      describe('when the relationship has a start date in the past', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1',
            start_date: previousWeek
          });
        });

        it('returns true', function () {
          expect(instance.isValid()).toBe(true);
        });
      });

      describe('when the relationship start date is today', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1',
            start_date: today
          });
        });

        it('returns true', function () {
          expect(instance.isValid()).toBe(true);
        });
      });

      describe('when the relationship has a start date in the future', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1',
            start_date: nextWeek
          });
        });

        it('returns false', function () {
          expect(instance.isValid()).toBe(false);
        });
      });

      describe('when the relationship end date is in the future', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1',
            end_date: nextWeek
          });
        });

        it('returns true', function () {
          expect(instance.isValid()).toBe(true);
        });
      });

      describe('when the relationship end date is today', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1',
            end_date: today
          });
        });

        it('returns true', function () {
          expect(instance.isValid()).toBe(true);
        });
      });

      describe('when the relationship end date is in the past', function () {
        beforeEach(function () {
          instance = RelationshipInstance.init({
            is_active: '1',
            end_date: previousWeek
          });
        });

        it('returns false', function () {
          expect(instance.isValid()).toBe(false);
        });
      });
    });
  });
});
