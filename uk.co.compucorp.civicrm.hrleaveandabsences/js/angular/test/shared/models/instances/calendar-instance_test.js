define([
  'mocks/data/work-pattern-data',
  'common/moment',
  'leave-absences/shared/models/instances/calendar-instance',
], function (mockData, moment) {
  'use strict';

  describe('CalendarInstance', function () {
    var CalendarInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject([
      'CalendarInstance',
      function (_CalendarInstance_) {
        CalendarInstance = _CalendarInstance_.init({
          days: mockData.daysData().values
        }, true);
      }]
    ));

    describe('test date functions', function () {
      var dateToSearch;

      function testOutofRangeDate() {
        dateToSearch = moment('2017-12-12');
        var workingDayFn = function () {
          CalendarInstance.isWorkingDay(dateToSearch)
        };

        expect(workingDayFn).toThrow(new Error('Date not found'));
      }

      function getDate(dayType) {
        return mockData.daysData().values.find(function (data) {
          return data.type.name === dayType;
        })
      }

      describe('isWorkingDay()', function () {

        it('return true if it is a working day', function () {
          dateToSearch = moment(getDate("working_day").date);
          expect(CalendarInstance.isWorkingDay(dateToSearch)).toBe(true);
        });

        it('return false if it is a not working day', function () {
          dateToSearch = moment(getDate("non_working_day").date);
          expect(CalendarInstance.isWorkingDay(dateToSearch)).toBe(false);
        });

        it('throws error if date is not found', testOutofRangeDate);
      });

      describe('isNonWorkingDay()', function () {

        it('return true if it is a non working day', function () {
          dateToSearch = moment(getDate("non_working_day").date);
          expect(CalendarInstance.isNonWorkingDay(dateToSearch)).toBe(true);
        });

        it('return false if it is a not non working day', function () {
          dateToSearch = moment(getDate("working_day").date);
          expect(CalendarInstance.isNonWorkingDay(dateToSearch)).toBe(false);
        });

        it('throws error if date is not found', testOutofRangeDate);
      });

      describe('isWeekend()', function () {

        it('return true if it is a weekend', function () {
          dateToSearch = moment(getDate("weekend").date);
          expect(CalendarInstance.isWeekend(dateToSearch)).toBe(true);
        });

        it('return false if it is a not weekend', function () {
          dateToSearch = moment(getDate("working_day").date);
          expect(CalendarInstance.isWeekend(dateToSearch)).toBe(false);
        });

        it('throws error if date is not found', testOutofRangeDate);
      });
    })
  });
});
