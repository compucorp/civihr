define([
  'common/moment',
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/instances/calendar-instance',
], function (moment, mockData) {
  'use strict';

  describe('CalendarInstance', function () {
    var CalendarInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject([
      'CalendarInstance',
      function (_CalendarInstance_) {
        CalendarInstance = _CalendarInstance_.init(mockData.daysData().values[0]);
      }]
    ));

    describe('init()', function () {
      var key, date;

      beforeEach(function () {
        //check with first key of the object
        key = Object.keys(CalendarInstance.days)[0];
        date = moment(CalendarInstance.days[key].date).valueOf().toString();
      });

      it('dates arrays has been converted to an object with proper timestamp values', function () {
        expect(key).toBe(date);
      })
    });

    describe('test date functions', function () {
      var dateToSearch;

      describe('isWorkingDay()', function () {

        it('return true if it is a working day', function () {
          dateToSearch = moment(getDate("working_day").date);
          expect(CalendarInstance.isWorkingDay(dateToSearch)).toBe(true);
        });

        it('return false if it is a not working day', function () {
          dateToSearch = moment(getDate("non_working_day").date);
          expect(CalendarInstance.isWorkingDay(dateToSearch)).toBe(false);
        });
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
      });

      describe('isWeekend()', function () {

        it('return true if it is a weekend', function () {
          dateToSearch = moment((getDate("weekend").date));
          expect(CalendarInstance.isWeekend(dateToSearch)).toBe(true);
        });

        it('return false if it is a not weekend', function () {
          dateToSearch = moment(getDate("working_day").date);
          expect(CalendarInstance.isWeekend(dateToSearch)).toBe(false);
        });
      });

      function getDate(dayType) {
        return mockData.daysData().values[0].calendar.find(function (data) {
          return data.type.name === dayType;
        });
      }
    })
  });
});
