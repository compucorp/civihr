define([
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/calendar-model',
], function (mockData) {
  'use strict';

  describe('Calendar', function () {
    var Calendar,
      WorkPatternAPI,
      $q,
      $rootScope;

    beforeEach(module('leave-absences.models'));

    beforeEach(inject(function (_Calendar_, _WorkPatternAPI_, _$rootScope_, _$q_) {
      Calendar = _Calendar_;
      WorkPatternAPI = _WorkPatternAPI_;
      $rootScope = _$rootScope_;
      $q = _$q_;

      spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
    }));

    afterEach(function () {
      $rootScope.$apply();
    });

    describe('getCalendar()', function () {
      var CalendarPromise,
        deferred;

      function commonSetUp(returnData) {
        deferred = $q.defer();
        deferred.resolve(returnData);
        WorkPatternAPI.getCalendar.and.returnValue(deferred.promise);

        CalendarPromise = Calendar.get(jasmine.any(String), jasmine.any(String), jasmine.any(Object));
      }

      it('calls equivalent API method', function () {
        commonSetUp(mockData.daysData());
        CalendarPromise.then(function () {
          expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
        });
      });

      it('returns model instances when request is successful', function () {
        commonSetUp(mockData.daysData());
        CalendarPromise.then(function (response) {
          expect('days' in response).toBe(true);
        });
      });

      it('returns error object when request is not successful', function () {
        commonSetUp(mockData.errorData());
        CalendarPromise.then(function (response) {
          expect(response).toBe(mockData.errorData());
        });
      });
    });
  });
});
