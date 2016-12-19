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
        .respond(mockData.daysData());
    }));

    describe('calendarData()', function () {
      var workPatternPromise,
        dummyContactId,
        dummyPeriodId;

      beforeEach(function () {
        spyOn(WorkPatternAPI, 'sendGET').and.callThrough();
        dummyContactId = "1";
        dummyPeriodId = "2";
        workPatternPromise = WorkPatternAPI.getCalendar(dummyContactId, dummyPeriodId, jasmine.any(Object));
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the sendGET() method', function () {
        expect(WorkPatternAPI.sendGET).toHaveBeenCalled();
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[0]).toBe('WorkPattern');
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[1]).toBe('getcalendar');
      });

      it('passes the contactId and periodId to the api', function () {
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[2].contact_id).toBe(dummyContactId);
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[2].period_id).toBe(dummyPeriodId);
      });

      it('returns calendar data', function () {
        workPatternPromise.then(function (response) {
          expect(response).toEqual(mockData.daysData());
        });
      });
    });
  });
});
