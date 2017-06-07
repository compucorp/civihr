/* eslint-env amd, jasmine */
/* global inject */

define([
  'common/lodash',
  'mocks/data/work-pattern-data',
  'leave-absences/shared/models/calendar-model'
], function (_, workPatternMocked) {
  'use strict';

  describe('Calendar', function () {
    var $q, $rootScope, Calendar, WorkPatternAPI;

    beforeEach(module('leave-absences.models'));
    beforeEach(inject(function (_$q_, _$rootScope_, _Calendar_, _WorkPatternAPI_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      Calendar = _Calendar_;
      WorkPatternAPI = _WorkPatternAPI_;
    }));

    afterEach(function () {
      $rootScope.$digest();
    });

    describe('get()', function () {
      var promise;

      beforeEach(function () {
        spyOn(WorkPatternAPI, 'getCalendar').and.returnValue($q.resolve(workPatternMocked.getCalendar));
      });

      describe('basic tests', function () {
        beforeEach(function () {
          Calendar.get(jasmine.any(String), jasmine.any(String));
        });

        it('calls the equivalent API method', function () {
          expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
        });
      });

      describe('resolved value', function () {
        describe('when passing a single contact id', function () {
          beforeEach(function () {
            promise = Calendar.get(jasmine.any(String), jasmine.any(String));
          });

          it('resolves to a single CalendarInstance', function () {
            promise.then(function (response) {
              expect(_.isArray(response)).toBe(false);
              expect(isInstance(response)).toBe(true);
            });
          });
        });

        describe('when passing multiple contact ids', function () {
          beforeEach(function () {
            promise = Calendar.get([jasmine.any(String), jasmine.any(String)], jasmine.any(String));
          });

          it('resolves to multiple CalendarInstances', function () {
            promise.then(function (response) {
              expect(_.isArray(response)).toBe(true);
              expect(isInstance(response[0])).toBe(true);
              expect(isInstance(response[1])).toBe(true);
            });
          });
        });

        /**
         * Checks if the given object is a CalendarInstance
         *
         * @param  {Object}  instance
         * @return {Boolean}
         */
        function isInstance (instance) {
          return !!(instance.fromAPI && instance.toAPI && instance.days);
        }
      });
    });
  });
});
