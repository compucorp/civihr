define([
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/instances/calendar-instance',
], function (mockData) {
  'use strict';

  describe('CalendarInstance', function () {
    var CalendarInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject([
      'CalendarInstance',
      function (_CalendarInstance_) {
        CalendarInstance = _CalendarInstance_.init(mockData.daysData().values);
      }]
    ));

    describe('init()', function () {
      var key, date;

      beforeEach(function () {
        //check with first key of the object
        key = Object.keys(CalendarInstance.days)[0];
        date = new Date(CalendarInstance.days[key].date).getTime().toString();
      });

      it('dates arrays has been converted to an object with proper timestamp values', function () {
        expect(key).toBe(date);
      })
    });

    describe('test date functions', function () {
      var dateToSearch;

      describe('isWorkingDay()', function () {

        it('return true if it is a working day', function () {
          dateToSearch = new Date(getDate("working_day").date);
          expect(CalendarInstance.isWorkingDay(dateToSearch)).toBe(true);
        });

        it('return false if it is a not working day', function () {
          dateToSearch = new Date(getDate("non_working_day").date);
          expect(CalendarInstance.isWorkingDay(dateToSearch)).toBe(false);
        });

        it('throws error if date is not found', testOutofRangeDate);
      });

      describe('isNonWorkingDay()', function () {

        it('return true if it is a non working day', function () {
          dateToSearch = new Date(getDate("non_working_day").date);
          expect(CalendarInstance.isNonWorkingDay(dateToSearch)).toBe(true);
        });

        it('return false if it is a not non working day', function () {
          dateToSearch = new Date(getDate("working_day").date);
          expect(CalendarInstance.isNonWorkingDay(dateToSearch)).toBe(false);
        });

        it('throws error if date is not found', testOutofRangeDate);
      });

      describe('isWeekend()', function () {

        it('return true if it is a weekend', function () {
          dateToSearch = new Date(getDate("weekend").date);
          expect(CalendarInstance.isWeekend(dateToSearch)).toBe(true);
        });

        it('return false if it is a not weekend', function () {
          dateToSearch = new Date(getDate("working_day").date);
          expect(CalendarInstance.isWeekend(dateToSearch)).toBe(false);
        });

        it('throws error if date is not found', testOutofRangeDate);
      });

      function testOutofRangeDate() {
        dateToSearch = new Date('2017-12-12');
        var workingDayFn = function () {
          CalendarInstance.isWorkingDay(dateToSearch)
        };

        expect(workingDayFn).toThrow(new Error('Date not found'));
      }

      function getDate(dayType) {
        return mockData.daysData().values.find(function (data) {
          return data.type.name === dayType;
        });
      }
    })
  });
});
