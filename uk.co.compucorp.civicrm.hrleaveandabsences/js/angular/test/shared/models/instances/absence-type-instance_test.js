define([
  'leave-absences/shared/models/instances/absence-type-instance',
], function () {
  'use strict'

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
      var attributes = {
        "id": "1",
        "title": "Holiday / Vacation",
        "weight": "1",
        "color": "#151D2C",
        "is_default": "1",
        "is_reserved": "1",
        "allow_request_cancelation": "3",
        "allow_overuse": "0",
        "must_take_public_holiday_as_leave": "1",
        "default_entitlement": "20",
        "add_public_holiday_to_entitlement": "1",
        "is_active": "1",
        "allow_accruals_request": "0",
        "allow_accrue_in_the_past": "0",
        "allow_carry_forward": "1",
        "max_number_of_days_to_carry_forward": "5",
        "carry_forward_expiration_duration": "12",
        "carry_forward_expiration_unit": "2"
      };

      beforeEach(function () {
        instance = AbsenceTypeInstance.init(attributes, true);
      });

      it('has expected data', function () {
        expect(instance.id).toBe(attributes.id);
        expect(instance.title).toEqual(jasmine.any(String));
        expect(instance.weight).toEqual(jasmine.any(String));
        expect(instance.color).toEqual(jasmine.any(String));
        expect(instance.is_default).toEqual(jasmine.any(String));
        expect(instance.is_reserved).toEqual(jasmine.any(String));
        expect(instance.allow_request_cancelation).toEqual(jasmine.any(String));
        expect(instance.allow_overuse).toEqual(jasmine.any(String));
        expect(instance.must_take_public_holiday_as_leave).toEqual(jasmine.any(String));
        expect(instance.default_entitlement).toEqual(jasmine.any(String));
        expect(instance.add_public_holiday_to_entitlement).toEqual(jasmine.any(String));
        expect(instance.is_active).toEqual(jasmine.any(String));
        expect(instance.allow_accruals_request).toEqual(jasmine.any(String));
        expect(instance.allow_accrue_in_the_past).toEqual(jasmine.any(String));
        expect(instance.allow_carry_forward).toEqual(jasmine.any(String));
      });
    });
  });
});
