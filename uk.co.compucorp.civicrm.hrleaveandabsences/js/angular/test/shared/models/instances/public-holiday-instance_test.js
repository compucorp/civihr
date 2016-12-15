define([
  'mocks/data/public-holiday-data',
  'leave-absences/shared/models/instances/public-holiday-instance',
], function (mockData) {
  'use strict'

  describe('PublicHolidayInstance', function () {
    var PublicHolidayInstance, ModelInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject(function (_PublicHolidayInstance_, _ModelInstance_) {
      PublicHolidayInstance = _PublicHolidayInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(PublicHolidayInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('init()', function () {
      var instance;
      var attributes = mockData.all().values[0];

      beforeEach(function () {
        instance = PublicHolidayInstance.init(attributes, true);
      });

      it('has expected data', function () {
        expect(instance.id).toBe(attributes.id);
        expect(instance.title).toEqual(attributes.title);
        expect(instance.date).toEqual(attributes.date);
        expect(instance.is_active).toEqual(attributes.is_active);
      });
    });
  });
});
