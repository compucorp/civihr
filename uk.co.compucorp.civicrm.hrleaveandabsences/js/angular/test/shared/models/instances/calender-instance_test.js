define([
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/instances/calender-instance',
], function (mockData) {
  'use strict';

  describe('CalenderInstance', function () {
    var CalenderInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject([
      "CalenderInstance",
      function (_CalenderInstance_) {
        CalenderInstance = _CalenderInstance_;
        CalenderInstance.calenderData = mockData.calenderData().values;
      }]
    ));

    describe('isWorkingDay()', function () {

      var dateToSearch;

      it('return true if it is a working day', function () {
        dateToSearch = mockData.calenderData().values[0].date;
        expect(CalenderInstance.isWorkingDay(dateToSearch)).toBe(true);
      });

      it('return false if it is a not working day', function () {
        dateToSearch = mockData.calenderData().values[1].date;
        expect(CalenderInstance.isWorkingDay(dateToSearch)).toBe(false);
      });

      it('throws error if date is not found', function () {
        dateToSearch = "2017-12-12";
        var workingDayFn = function () {
          CalenderInstance.isWorkingDay(dateToSearch)
        };

        expect(workingDayFn).toThrow(new Error("Date not found"));
      });
    });

    describe('isNonWorkingDay()', function () {

      var dateToSearch;

      it('return true if it is a non working day', function () {
        dateToSearch = mockData.calenderData().values[1].date;
        expect(CalenderInstance.isNonWorkingDay(dateToSearch)).toBe(true);
      });

      it('return false if it is a not non working day', function () {
        dateToSearch = mockData.calenderData().values[0].date;
        expect(CalenderInstance.isNonWorkingDay(dateToSearch)).toBe(false);
      });

      it('throws error if date is not found', function () {
        dateToSearch = "2017-12-12";
        var workingDayFn = function () {
          CalenderInstance.isNonWorkingDay(dateToSearch)
        };

        expect(workingDayFn).toThrow(new Error("Date not found"));
      });
    });

    describe('isWeekend()', function () {

      var dateToSearch;

      it('return true if it is a weekend', function () {
        dateToSearch = mockData.calenderData().values[2].date;
        expect(CalenderInstance.isWeekend(dateToSearch)).toBe(true);
      });

      it('return false if it is a not weekend', function () {
        dateToSearch = mockData.calenderData().values[0].date;
        expect(CalenderInstance.isWeekend(dateToSearch)).toBe(false);
      });

      it('throws error if date is not found', function () {
        dateToSearch = "2017-12-12";
        var workingDayFn = function () {
          CalenderInstance.isWeekend(dateToSearch)
        };
        expect(workingDayFn).toThrow(new Error("Date not found"));
      });
    });
  });
});
