/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/absence-type-data',
  'leave-absences/shared/instances/absence-type.instance'
], function (_, mockData) {
  'use strict';

  describe('AbsenceTypeInstance', function () {
    var AbsenceTypeInstance, ModelInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject(function (_AbsenceTypeInstance_, _ModelInstance_) {
      AbsenceTypeInstance = _AbsenceTypeInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(AbsenceTypeInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('init()', function () {
      var instance;
      var attributes = mockData.all().values[0];

      beforeEach(function () {
        instance = AbsenceTypeInstance.init(attributes, true);
      });

      it('has expected data', function () {
        expect(instance.id).toBe(attributes.id);
        expect(instance.title).toEqual(attributes.title);
        expect(instance.weight).toEqual(attributes.weight);
        expect(instance.color).toEqual(attributes.color);
        expect(instance.is_default).toEqual(attributes.is_default);
        expect(instance.is_reserved).toEqual(attributes.is_reserved);
        expect(instance.allow_request_cancelation).toEqual(attributes.allow_request_cancelation);
        expect(instance.allow_overuse).toEqual(attributes.allow_overuse);
        expect(instance.must_take_public_holiday_as_leave).toEqual(attributes.must_take_public_holiday_as_leave);
        expect(instance.default_entitlement).toEqual(attributes.default_entitlement);
        expect(instance.add_public_holiday_to_entitlement).toEqual(attributes.add_public_holiday_to_entitlement);
        expect(instance.is_active).toEqual(attributes.is_active);
        expect(instance.allow_accruals_request).toEqual(attributes.allow_accruals_request);
        expect(instance.allow_accrue_in_the_past).toEqual(attributes.allow_accrue_in_the_past);
        expect(instance.allow_carry_forward).toEqual(attributes.allow_carry_forward);
      });
    });
  });
});
