/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/data/work-pattern-data',
  'mocks/data/option-group-mock-data',
  'leave-absences/shared/instances/calendar.instance',
  'mocks/apis/option-group-api-mock'
], function (_, moment, workPatternMocked, optionGroupMocked) {
  'use strict';

  describe('CalendarInstance', function () {
    var $rootScope, OptionGroup, instance, mockedCalendar, promise;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks'));
    beforeEach(inject(function (_$rootScope_, _OptionGroup_, OptionGroupAPIMock) {
      $rootScope = _$rootScope_;
      OptionGroup = _OptionGroup_;

      spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
        return OptionGroupAPIMock.valuesOf(name);
      });
    }));
    beforeEach(inject(function (CalendarInstance) {
      mockedCalendar = workPatternMocked.getCalendar.values[0];
      instance = CalendarInstance.init(mockedCalendar);
    }));

    describe('on injection', function () {
      it('does not fetches the list of day types', function () {
        expect(OptionGroup.valuesOf).not.toHaveBeenCalled();
      });
    });

    afterEach(function () {
      $rootScope.$digest();
    });

    describe('init()', function () {
      var key, date;

      beforeEach(function () {
        key = Object.keys(instance.days)[0];
        date = moment(instance.days[key].date).valueOf();
      });

      it('keeps the `contact_id` property', function () {
        expect(instance.contact_id).toBeDefined();
        expect(instance.contact_id).toBe(mockedCalendar.contact_id);
      });

      it('removes the `calendar` property', function () {
        expect(instance.calendar).not.toBeDefined();
      });

      it('creates the `days` property, an object with timestamps as keys', function () {
        expect(+key).toBe(date);
      });
    });

    describe('loading of day types OptionValues', function () {
      describe('when it is the first time a `isXYZ` method is called', function () {
        beforeEach(function () {
          callRandomIsXYZMethod();
        });

        it('fetches the list of day types', function () {
          expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_work_day_type');
        });
      });

      describe('when it is not the first time a `isXYZ` method is called', function () {
        var promise;

        beforeEach(function () {
          callRandomIsXYZMethod();
          OptionGroup.valuesOf.calls.reset();

          promise = callRandomIsXYZMethod();
        });

        it('does not feth the list of day types', function () {
          expect(OptionGroup.valuesOf).not.toHaveBeenCalled();
        });

        it('still returns a value', function () {
          promise.then(function (value) {
            expect(value).toBeDefined();
          });
        });
      });

      function callRandomIsXYZMethod () {
        var isXYZMethods = ['isNonWorkingDay', 'isWeekend', 'isWorkingDay'];

        return instance[_.sample(isXYZMethods)](dateOfType('working_day'));
      }
    });

    describe('isWorkingDay()', function () {
      it('returns a promise', function () {
        expect(instance.isWorkingDay(jasmine.any(String)).then).toBeDefined();
      });

      describe('when the day is a working day"', function () {
        beforeEach(function () {
          promise = instance.isWorkingDay(dateOfType('working_day'));
        });

        it('returns true', function () {
          promise.then(function (result) {
            expect(result).toBe(true);
          });
        });
      });

      describe('when the day is not a working day', function () {
        beforeEach(function () {
          promise = instance.isWorkingDay(dateOfType('weekend'));
        });

        it('returns false', function () {
          promise.then(function (result) {
            expect(result).toBe(false);
          });
        });
      });
    });

    describe('isNonWorkingDay()', function () {
      it('returns a promise', function () {
        expect(instance.isNonWorkingDay(jasmine.any(String)).then).toBeDefined();
      });

      describe('when the day is not a working day', function () {
        beforeEach(function () {
          promise = instance.isNonWorkingDay(dateOfType('non_working_day'));
        });

        it('returns false', function () {
          promise.then(function (result) {
            expect(result).toBe(true);
          });
        });
      });

      describe('when the day is a working day', function () {
        beforeEach(function () {
          promise = instance.isNonWorkingDay(dateOfType('working_day'));
        });

        it('returns false', function () {
          promise.then(function (result) {
            expect(result).toBe(false);
          });
        });
      });
    });

    describe('isWeekend()', function () {
      it('returns a promise', function () {
        expect(instance.isWeekend(jasmine.any(String)).then).toBeDefined();
      });

      describe('when the day is a weekend', function () {
        beforeEach(function () {
          promise = instance.isWeekend(dateOfType('weekend'));
        });

        it('returns false', function () {
          promise.then(function (result) {
            expect(result).toBe(true);
          });
        });
      });

      describe('when the day is not a weekend', function () {
        beforeEach(function () {
          promise = instance.isWeekend(dateOfType('non_working_day'));
        });

        it('returns false', function () {
          promise.then(function (result) {
            expect(result).toBe(false);
          });
        });
      });
    });

    /**
     * Returns the date of a day in the calendar that matches the given type name
     *
     * @param  {string} typeName
     * @return {string}
     */
    function dateOfType (typeName) {
      return moment(_.find(instance.days, function (day) {
        return day.type === dayTypeByName(typeName).value;
      }).date);
    }

    /**
     * Finds a day type Option Value based on its name
     *
     * @param  {string} name
     * @return {object}
     */
    function dayTypeByName (name) {
      var dayTypes = optionGroupMocked.getCollection('hrleaveandabsences_work_day_type');

      return _.find(dayTypes, function (dayType) {
        return dayType.name === name;
      });
    }
  });
});
