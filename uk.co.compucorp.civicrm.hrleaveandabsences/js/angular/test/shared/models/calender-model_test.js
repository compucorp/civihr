define([
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/calender-model',
], function (mockData) {
  'use strict';

  describe('Calender', function () {
    var Calender,
      WorkPatternAPI,
      $q,
      $rootScope;

    beforeEach(module('leave-absences.models'));

    beforeEach(inject(function (_Calender_, _WorkPatternAPI_, _$rootScope_, _$q_) {
      Calender = _Calender_;
      WorkPatternAPI = _WorkPatternAPI_;
      $rootScope = _$rootScope_;
      $q = _$q_;

      spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
    }));

    afterEach(function () {
      $rootScope.$apply();
    });

    describe('getCalendar()', function () {
      var CalenderPromise,
        deferred;

      function commonSetUp(returnData) {
        deferred = $q.defer();
        WorkPatternAPI.getCalendar.and.returnValue(deferred.promise);
        CalenderPromise = Calender.getCalendar();
        deferred.resolve(returnData);
      }

      it('calls equivalent API method', function () {
        commonSetUp(mockData.calenderData());
        CalenderPromise.then(function () {
          expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
        });
      });

      it('returns model instances when request is successful', function () {
        commonSetUp(mockData.calenderData());
        CalenderPromise.then(function (response) {
          expect('calenderData' in response).toBe(true);
        });
      });

      it('returns error object when request is not successful', function () {
        commonSetUp(mockData.errorData());
        CalenderPromise.then(function (response) {
          expect(response).toBe(mockData.errorData());
        });
      });
    });
  });
});
