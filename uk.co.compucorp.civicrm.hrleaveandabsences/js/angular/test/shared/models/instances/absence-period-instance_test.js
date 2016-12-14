define([
  'mocks/data/absence-period-data',
  'leave-absences/shared/models/instances/absence-period-instance',
], function (mockData) {
  'use strict'

  describe('AbsencePeriodInstance', function () {
    var AbsencePeriodInstance, ModelInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject(function (_AbsencePeriodInstance_, _ModelInstance_) {
      AbsencePeriodInstance = _AbsencePeriodInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(AbsencePeriodInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('init()', function () {
      var instance;
      var attributes = mockData.all().values[0];

      beforeEach(function () {
        instance = AbsencePeriodInstance.init(attributes, true);
      });

      it('has expected data', function () {
        expect(instance.id).toBe(attributes.id);
        expect(instance.title).toEqual(attributes.title);
        expect(instance.start_date).toEqual(attributes.start_date);
        expect(instance.end_date).toEqual(attributes.end_date);
        expect(instance.weight).toEqual(attributes.weight);
      });

      it('has current attribute set to true', function () {
        expect(instance.current).toBe(true);
      });

      describe('with past period', function () {
        var instance;
        var attributes = mockData.past_all().values[0];

        beforeEach(function () {
          instance = AbsencePeriodInstance.init(attributes, true);
        });

        it('has current attribute set to false', function () {
          expect(instance.current).toBe(false);
        });
      });
    });

    describe('isInPeriod()', function () {
      var instance, mockCurrentDate = '2016-12-14';
      var attributes = mockData.all().values[0];

      beforeEach(function () {
        instance = AbsencePeriodInstance.init(attributes, true);
      });

      it('returns true for date in current period ', function () {
        expect(instance.isInPeriod(mockCurrentDate)).toBe(true);
      });

      describe('with past period', function () {
        var instance;
        var attributes = mockData.past_all().values[0];

        beforeEach(function () {
          instance = AbsencePeriodInstance.init(attributes, true);
        });

        it('returns true for date in current period ', function () {
          expect(instance.isInPeriod(mockCurrentDate)).toBe(false);
        });
      });
    });
  });
});
