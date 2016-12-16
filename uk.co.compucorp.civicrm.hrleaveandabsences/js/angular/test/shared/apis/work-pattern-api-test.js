define([
  'mocks/data/work-pattern-data',
  'leave-absences/shared/apis/work-pattern-api',
], function (mockData) {
  'use strict';

  describe('WorkPatternAPI', function () {
    var WorkPatternAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_WorkPatternAPI_, _$httpBackend_) {
      WorkPatternAPI = _WorkPatternAPI_;
      $httpBackend = _$httpBackend_;

      //Intercept backend calls for WorkPatternAPI.getCalendar
      $httpBackend.whenGET(/action\=getcalendar&entity\=WorkPattern/)
        .respond(mockData.calenderData());
    }));

    describe('calenderData()', function () {
      var workPatternPromise;

      beforeEach(function () {
        spyOn(WorkPatternAPI, 'sendGET').and.callThrough();
        workPatternPromise = WorkPatternAPI.getCalendar();
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the sendGET() method', function () {
        expect(WorkPatternAPI.sendGET).toHaveBeenCalled();
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[0]).toBe('WorkPattern');
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[1]).toBe('getcalendar');
      });

      it('returns calender data', function () {
        workPatternPromise.then(function (response) {
          expect(response).toEqual(mockData.calenderData());
        });
      });
    });
  });
});
