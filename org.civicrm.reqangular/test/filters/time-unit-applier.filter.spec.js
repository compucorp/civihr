/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/filters/time-unit-applier.filter'
], function (angular, moment) {
  'use strict';

  describe('timeUnitApplier', function () {
    var timeUnitApplier;

    beforeEach(module('common.filters'));
    beforeEach(inject(['$filter',
      function ($filter) {
        timeUnitApplier = $filter('timeUnitApplier');
      }
    ]));

    it('is defined', function () {
      expect(timeUnitApplier).toBeDefined();
    });

    describe('when non-parsable value is passed', function () {
      var invalidValues = [null, false, true, NaN, '', 'string', ' ', [], {}];

      invalidValues.forEach(function (invalidValue) {
        it('it throws an error', function () {
          expect(function () { timeUnitApplier(invalidValue, 'days'); })
            .toThrow(jasmine.any(Error));
        });
      });
    });

    describe('when non-supported unit passed', function () {
      it('it throws an error', function () {
        expect(function () { timeUnitApplier(0, 'sols'); })
          .toThrow(jasmine.any(Error));
      });
    });

    describe('when undefined value is passed', function () {
      it('it returns empty string', function () {
        expect(timeUnitApplier(undefined, 'days')).toBe('0d');
      });
    });

    describe('when unit is "days"', function () {
      it('it returns expected strings', function () {
        expect(timeUnitApplier(-2, 'days')).toBe('-2d');
        expect(timeUnitApplier(0, 'days')).toBe('0d');
        expect(timeUnitApplier(0.3, 'days')).toBe('0.3d');
        expect(timeUnitApplier('0.3', 'days')).toBe('0.3d');
        expect(timeUnitApplier('.3', 'days')).toBe('0.3d');
        expect(timeUnitApplier(0.99, 'days')).toBe('0.99d');
        expect(timeUnitApplier(1, 'days')).toBe('1d');
        expect(timeUnitApplier(5, 'days')).toBe('5d');
        expect(timeUnitApplier(7.78, 'days')).toBe('7.78d');
      });
    });

    describe('when unit is "hours"', function () {
      it('it returns expected values', function () {
        expect(timeUnitApplier(-2, 'hours')).toBe('-2h');
        expect(timeUnitApplier(-0.5, 'hours')).toBe('-30m');
        expect(timeUnitApplier(-0.01, 'hours')).toBe('-15m');
        expect(timeUnitApplier(0, 'hours')).toBe('0h');
        expect(timeUnitApplier(0.01, 'hours')).toBe('15m');
        expect(timeUnitApplier(0.3, 'hours')).toBe('30m');
        expect(timeUnitApplier('0.3', 'hours')).toBe('30m');
        expect(timeUnitApplier('.3', 'hours')).toBe('30m');
        expect(timeUnitApplier(0.874, 'hours')).toBe('1h');
        expect(timeUnitApplier(0.875, 'hours')).toBe('1h');
        expect(timeUnitApplier(0.876, 'hours')).toBe('1h');
        expect(timeUnitApplier(0.99, 'hours')).toBe('1h');
        expect(timeUnitApplier(1, 'hours')).toBe('1h');
        expect(timeUnitApplier(1.01, 'hours')).toBe('1h 15m');
        expect(timeUnitApplier(1.124, 'hours')).toBe('1h 15m');
        expect(timeUnitApplier(1.125, 'hours')).toBe('1h 15m');
        expect(timeUnitApplier(1.126, 'hours')).toBe('1h 15m');
        expect(timeUnitApplier(5, 'hours')).toBe('5h');
        expect(timeUnitApplier(7.78, 'hours')).toBe('8h');
      });
    });
  });
});
