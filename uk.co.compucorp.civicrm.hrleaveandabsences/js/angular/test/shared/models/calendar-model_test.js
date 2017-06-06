/* eslint-env amd, jasmine */
/* global inject */

define([
  'common/lodash',
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/calendar-model'
], function (_, mockData) {
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

    describe('get()', function () {
      var CalendarPromise,
        deferred;

      function commonSetUp (returnData) {
        deferred = $q.defer();
        deferred.resolve(returnData);
        WorkPatternAPI.getCalendar.and.returnValue(deferred.promise);

        CalendarPromise = Calendar.get(jasmine.any(String), jasmine.any(String), jasmine.any(Object));
      }

      describe('basic tests', function () {
        it('calls equivalent API method', function () {
          commonSetUp(mockData.daysData());
          CalendarPromise.then(function () {
            expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
          });
        });
      });

      describe('when passing a single contact id', function () {
        it('returns a single CalendarInstance', function () {
          commonSetUp(mockData.daysData());
          CalendarPromise.then(function (response) {
            expect(_.isArray(response)).toBe(false);
            expect('days' in response).toBe(true);
          });
        });
      });

      describe('when passing multiple contact ids', function () {
        beforeEach(function () {
          WorkPatternAPI.getCalendar.and.returnValue($q.resolve(mockData.daysData()));
          CalendarPromise = Calendar.get([jasmine.any(String), jasmine.any(String)], jasmine.any(String));
        });

        it('returns multiple CalendarInstances', function () {
          CalendarPromise.then(function (response) {
            expect(_.isArray(response)).toBe(true);
            expect('days' in response[0]).toBe(true);
            expect('days' in response[1]).toBe(true);
          });
        });
      });
    });
  });
});
