/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/work-pattern-data',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/models/calendar-model'
], function (_, workPatternMocked) {
  'use strict';

  describe('Calendar', function () {
    var $q, $rootScope, Calendar, WorkPatternAPI;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks'));
    beforeEach(inject(function (OptionGroup, OptionGroupAPIMock) {
      spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
        return OptionGroupAPIMock.valuesOf(name);
      });
    }));
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
        var contactId = '1';
        var startDate = '2017-01-01';
        var endDate = '2017-12-31';
        var params = { foo: 'foo', bar: 'bar' };

        beforeEach(function () {
          Calendar.get(contactId, startDate, endDate, params);
        });

        it('calls the equivalent API method', function () {
          expect(WorkPatternAPI.getCalendar).toHaveBeenCalledWith(contactId, startDate, endDate, params);
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
              expect(response.every(function (instance) {
                return isInstance(instance);
              })).toBe(true);
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
